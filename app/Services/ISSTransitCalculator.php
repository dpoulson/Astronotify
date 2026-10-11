<?php

namespace App\Services;

use App\Libs\SunCalc;
use App\Models\ISSTransit;
use App\Models\Location;
use App\Models\Setting;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Predict;
use Predict_QTH;
use Predict_Sat;
use Predict_Time;
use Predict_TLE;

/**
 * Class ISSTransitCalculator
 *
 * Predicts orbital passes of the International Space Station (NORAD CATNR 25544)
 * and calculates high-precision solar and lunar transits/conjunctions using SGP4 propagation.
 */
class ISSTransitCalculator
{
    /**
     * Calculate and synchronize upcoming ISS solar/lunar transits for the given location.
     *
     * @param  Location  $location  Target observation location.
     * @return array<int, array{id: int, type: string, time: string, separation_degrees: float, altitude_degrees: float, azimuth_degrees: float, is_exact_transit: bool, cloud_cover_percent: int|null, notified_at: Carbon|null}>
     */
    public function calculateForLocation(Location $location): array
    {
        if (! $location->is_active || (! $location->notify_iss_sun_transit && ! $location->notify_iss_moon_transit)) {
            ISSTransit::where('location_id', $location->id)->delete();

            return [];
        }

        // Fetch/Cache TLE for 4 hours to avoid hitting CelesTrak repeatedly on rapid saves
        $tleBody = Cache::remember('iss_tle_data', 14400, function () {
            $response = Http::get('https://celestrak.org/NORAD/elements/gp.php?CATNR=25544&FORMAT=tle');

            return $response->successful() ? $response->body() : null;
        });

        if (! $tleBody) {
            Log::error('ISS Transit Calculator: Failed to download TLE.');

            return [];
        }

        $lines = explode("\n", trim($tleBody));
        if (count($lines) < 3) {
            Log::error('ISS Transit Calculator: Invalid TLE response format.');

            return [];
        }

        $tleName = trim($lines[0]);
        $tleLine1 = trim($lines[1]);
        $tleLine2 = trim($lines[2]);

        $tle = new Predict_TLE($tleName, $tleLine1, $tleLine2);
        $sat = new Predict_Sat($tle);
        $predict = new Predict;

        $forecastDays = (int) Setting::get('forecast_days', 7);
        $limitDeg = (float) Setting::get('conjunction_threshold', 0.75);
        $minAltitude = (float) Setting::get('transit_min_altitude', 5.0);
        $startJD = Predict_Time::get_current_daynum();

        // Load existing upcoming transit records to preserve notification timestamps
        $existingTransits = ISSTransit::where('location_id', $location->id)
            ->where('time', '>=', now()->subHours(2))
            ->get();
        $matchedTransitIds = [];

        $qth = new Predict_QTH;
        $qth->lat = (float) $location->latitude;
        $qth->lon = (float) $location->longitude;
        $qth->alt = (float) ($location->elevation ?? 0.0);

        try {
            $passes = $predict->get_passes($sat, $qth, $startJD, $forecastDays);
        } catch (Exception $e) {
            Log::error("Failed to calculate passes for location {$location->name}: ".$e->getMessage());

            return [];
        }

        $createdTransits = [];

        // Configure enabled celestial tracking targets
        $targetTypes = [];
        if ($location->notify_iss_sun_transit) {
            $targetTypes['sun'] = fn (DateTime $date) => SunCalc::getPosition($date, $qth->lat, $qth->lon);
        }
        if ($location->notify_iss_moon_transit) {
            $targetTypes['moon'] = fn (DateTime $date) => SunCalc::getMoonPosition($date, $qth->lat, $qth->lon);
        }

        foreach ($passes as $pass) {
            $dur = $pass->los - $pass->aos;
            if ($dur <= 0) {
                continue;
            }

            // Initialize target tracking data for this pass
            $targetData = [];
            foreach ($targetTypes as $type => $callback) {
                $targetData[$type] = [
                    'minSep' => 999.0,
                    'minTime' => null,
                    'minAlt' => 0.0,
                    'minAz' => 0.0,
                    'posCallback' => $callback,
                ];
            }

            // Coarse search (10-second intervals)
            $coarseStep = 10.0 / 86400.0;
            for ($t = $pass->aos; $t <= $pass->los; $t += $coarseStep) {
                try {
                    $predict->predict_calc($sat, $qth, $t);
                } catch (Exception $e) {
                    continue;
                }

                $unix = Predict_Time::daynum2unix($t);
                $date = new DateTime('@'.round($unix));

                $satEl = $sat->el + ($sat->el > 0 ? SunCalc::getRefractionDegrees($sat->el) : 0.0);
                $satAz = $sat->az;

                foreach ($targetData as $type => &$data) {
                    $pos = ($data['posCallback'])($date);
                    $sep = self::calculateSeparation($satEl, $satAz, $pos['altitude'], $pos['azimuth']);
                    if ($sep < $data['minSep']) {
                        $data['minSep'] = $sep;
                        $data['minTime'] = $t;
                        $data['minAlt'] = $pos['altitude'];
                        $data['minAltGeometric'] = $pos['altitude_geometric'] ?? $pos['altitude'];
                        $data['minAz'] = $pos['azimuth'];
                    }
                }
                unset($data);
            }

            // Fine search (0.2-second intervals within ±10s of closest approach)
            $fineStep = 0.2 / 86400.0;
            foreach ($targetData as $type => &$data) {
                if ($data['minTime'] !== null && $data['minSep'] < 3.0) {
                    $startFine = max($pass->aos, $data['minTime'] - (10.0 / 86400.0));
                    $endFine = min($pass->los, $data['minTime'] + (10.0 / 86400.0));

                    for ($t = $startFine; $t <= $endFine; $t += $fineStep) {
                        try {
                            $predict->predict_calc($sat, $qth, $t);
                        } catch (Exception $e) {
                            continue;
                        }

                        $unix = Predict_Time::daynum2unix($t);
                        $date = new DateTime('@'.round($unix));
                        $satEl = $sat->el + ($sat->el > 0 ? SunCalc::getRefractionDegrees($sat->el) : 0.0);
                        $satAz = $sat->az;
                        $pos = ($data['posCallback'])($date);
                        $sep = self::calculateSeparation($satEl, $satAz, $pos['altitude'], $pos['azimuth']);
                        if ($sep < $data['minSep']) {
                            $data['minSep'] = $sep;
                            $data['minTime'] = $t;
                            $data['minAlt'] = $pos['altitude'];
                            $data['minAltGeometric'] = $pos['altitude_geometric'] ?? $pos['altitude'];
                            $data['minAz'] = $pos['azimuth'];
                        }
                    }
                }
            }
            unset($data);

            // Record transits / conjunctions meeting user thresholds and above horizon
            foreach ($targetData as $type => $data) {
                if ($data['minTime'] !== null
                    && $data['minSep'] <= $limitDeg
                    && $data['minAlt'] >= $minAltitude
                    && ($data['minAltGeometric'] ?? $data['minAlt']) > 0) {
                    // Generate fine path points for orbital diagram (±30s around transit at 2s intervals)
                    $pathPoints = [];
                    $startPath = max($pass->aos, $data['minTime'] - (30.0 / 86400.0));
                    $endPath = min($pass->los, $data['minTime'] + (30.0 / 86400.0));
                    $pathStep = 2.0 / 86400.0;

                    for ($t = $startPath; $t <= $endPath; $t += $pathStep) {
                        try {
                            $predict->predict_calc($sat, $qth, $t);
                        } catch (Exception $e) {
                            continue;
                        }

                        $unix = Predict_Time::daynum2unix($t);
                        $date = new DateTime('@'.round($unix));
                        $pos = ($data['posCallback'])($date);
                        $satEl = $sat->el + ($sat->el > 0 ? SunCalc::getRefractionDegrees($sat->el) : 0.0);

                        $pathPoints[] = [
                            'dx' => round($sat->az - $pos['azimuth'], 4),
                            'dy' => round($satEl - $pos['altitude'], 4),
                        ];
                    }

                    $unix = Predict_Time::daynum2unix($data['minTime']);
                    $date = new DateTime('@'.round($unix));
                    $date->setTimezone(new DateTimeZone('UTC'));

                    $transitRecord = $this->upsertTransit(
                        $location,
                        $type,
                        $date,
                        $data['minSep'],
                        $data['minAlt'],
                        $data['minAz'],
                        $pathPoints,
                        $existingTransits,
                        $matchedTransitIds
                    );

                    $createdTransits[] = [
                        'id' => $transitRecord->id,
                        'type' => $type,
                        'time' => $date->format('Y-m-d\TH:i:s\Z'),
                        'separation_degrees' => round($data['minSep'], 4),
                        'altitude_degrees' => round($data['minAlt'], 2),
                        'azimuth_degrees' => round($data['minAz'], 2),
                        'is_exact_transit' => ($data['minSep'] <= 0.26),
                        'cloud_cover_percent' => $transitRecord->cloud_cover_percent,
                        'notified_at' => $transitRecord->notified_at,
                    ];
                }
            }
        }

        // Remove future transit records that were not matched (pass trajectory shifted or expired)
        $unmatchedIds = $existingTransits->pluck('id')->diff($matchedTransitIds);
        if ($unmatchedIds->isNotEmpty()) {
            ISSTransit::whereIn('id', $unmatchedIds)->delete();
        }

        // Clean up old past records
        ISSTransit::where('location_id', $location->id)->where('time', '<', now()->subHours(2))->delete();

        return $createdTransits;
    }

    /**
     * Persist or update an ISS transit record while preserving notification timestamps.
     *
     * @param  Location  $location  Parent location.
     * @param  string  $type  Target celestial body ('sun' or 'moon').
     * @param  DateTime  $date  Timestamp of closest approach in UTC.
     * @param  float  $sep  Angular separation in degrees.
     * @param  float  $alt  Altitude in degrees.
     * @param  float  $az  Azimuth in degrees.
     * @param  array<int, array{dx: float, dy: float}>|null  $pathPoints  Chord offset points.
     * @param  Collection<int, ISSTransit>  $existingTransits  Preloaded existing records.
     * @param  array<int, int>  $matchedTransitIds  Array tracking matched record IDs.
     */
    private function upsertTransit(
        Location $location,
        string $type,
        DateTime $date,
        float $sep,
        float $alt,
        float $az,
        ?array $pathPoints,
        Collection $existingTransits,
        array &$matchedTransitIds
    ): ISSTransit {
        $carbonDate = Carbon::instance($date);

        // Find existing record of same type within +/- 15 minutes that hasn't been matched yet
        $existing = $existingTransits->first(function (ISSTransit $t) use ($type, $carbonDate, $matchedTransitIds) {
            return $t->type === $type
                && ! in_array($t->id, $matchedTransitIds)
                && abs($carbonDate->diffInMinutes($t->time)) <= 15;
        });

        $cloudCover = $location->getCloudCoverAt($date) ?? ($existing?->cloud_cover_percent);
        $isExact = ($sep <= 0.26);

        if ($existing) {
            $existing->update([
                'time' => $date,
                'separation_degrees' => $sep,
                'altitude_degrees' => $alt,
                'azimuth_degrees' => $az,
                'is_exact_transit' => $isExact,
                'path_points' => $pathPoints ?: null,
                'cloud_cover_percent' => $cloudCover,
            ]);
            $matchedTransitIds[] = $existing->id;

            return $existing;
        }

        $newTransit = ISSTransit::create([
            'location_id' => $location->id,
            'type' => $type,
            'time' => $date,
            'separation_degrees' => $sep,
            'altitude_degrees' => $alt,
            'azimuth_degrees' => $az,
            'is_exact_transit' => $isExact,
            'path_points' => $pathPoints ?: null,
            'cloud_cover_percent' => $cloudCover,
            'notified_at' => null,
        ]);
        $matchedTransitIds[] = $newTransit->id;

        return $newTransit;
    }

    /**
     * Calculate great-circle angular separation between two horizontal coordinate pairs in degrees.
     *
     * @param  float|int  $el1  Altitude of object 1 (degrees).
     * @param  float|int  $az1  Azimuth of object 1 (degrees).
     * @param  float|int  $el2  Altitude of object 2 (degrees).
     * @param  float|int  $az2  Azimuth of object 2 (degrees).
     * @return float Angular separation in degrees.
     */
    public static function calculateSeparation(float|int $el1, float|int $az1, float|int $el2, float|int $az2): float
    {
        $r_el1 = deg2rad($el1);
        $r_el2 = deg2rad($el2);
        $r_az1 = deg2rad($az1);
        $r_az2 = deg2rad($az2);

        $cosTheta = sin($r_el1) * sin($r_el2) + cos($r_el1) * cos($r_el2) * cos($r_az1 - $r_az2);
        $cosTheta = max(-1.0, min(1.0, $cosTheta));

        return rad2deg(acos($cosTheta));
    }
}
