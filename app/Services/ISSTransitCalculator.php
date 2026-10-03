<?php

namespace App\Services;

use App\Models\Location;
use App\Models\ISSTransit;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ISSTransitCalculator
{
    public function calculateForLocation(Location $location): array
    {
        if (!$location->is_active || (!$location->notify_iss_sun_transit && !$location->notify_iss_moon_transit)) {
            ISSTransit::where('location_id', $location->id)->delete();
            return [];
        }

        // Fetch/Cache TLE for 4 hours to avoid hitting CelesTrak too often on multiple rapid edits
        $tleBody = Cache::remember('iss_tle_data', 14400, function () {
            $response = Http::get('https://celestrak.org/NORAD/elements/gp.php?CATNR=25544&FORMAT=tle');
            return $response->successful() ? $response->body() : null;
        });

        if (!$tleBody) {
            Log::error("ISS Transit Calculator: Failed to download TLE.");
            return [];
        }

        $lines = explode("\n", trim($tleBody));
        if (count($lines) < 3) {
            Log::error("ISS Transit Calculator: Invalid TLE response format.");
            return [];
        }

        $tleName = trim($lines[0]);
        $tleLine1 = trim($lines[1]);
        $tleLine2 = trim($lines[2]);



        $tle = new \Predict_TLE($tleName, $tleLine1, $tleLine2);
        $sat = new \Predict_Sat($tle);
        $predict = new \Predict();

        $forecastDays = (int) (Setting::where('key', 'forecast_days')->value('value') ?? 7);
        $startJD = \Predict_Time::get_current_daynum();

        // Load existing upcoming transit records to preserve notification state
        $existingTransits = ISSTransit::where('location_id', $location->id)
            ->where('time', '>=', now()->subHours(2))
            ->get();
        $matchedTransitIds = [];

        $qth = new \Predict_QTH();
        $qth->lat = (float) $location->latitude;
        $qth->lon = (float) $location->longitude;
        $qth->alt = (float) ($location->elevation ?? 0.0);

        try {
            $passes = $predict->get_passes($sat, $qth, $startJD, $forecastDays);
        } catch (\Exception $e) {
            Log::error("Failed to calculate passes for location {$location->name}: " . $e->getMessage());
            return [];
        }

        $createdTransits = [];

        foreach ($passes as $pass) {
            $dur = $pass->los - $pass->aos;
            if ($dur <= 0) {
                continue;
            }

            $minSunSep = 999.0;
            $minSunTime = null;
            $minSunAlt = 0;
            $minSunAz = 0;

            $minMoonSep = 999.0;
            $minMoonTime = null;
            $minMoonAlt = 0;
            $minMoonAz = 0;

            $sunPathPoints  = [];
            $moonPathPoints = [];

            // Coarse search
            $coarseStep = 10.0 / 86400.0;
            for ($t = $pass->aos; $t <= $pass->los; $t += $coarseStep) {
                try {
                    $predict->predict_calc($sat, $qth, $t);
                } catch (\Exception $e) {
                    continue;
                }

                $unix = \Predict_Time::daynum2unix($t);
                $date = new \DateTime("@" . round($unix));

                if ($location->notify_iss_sun_transit) {
                    $sunPos = \App\Libs\SunCalc::getPosition($date, $qth->lat, $qth->lon);
                    $sep = self::calculateSeparation($sat->el, $sat->az, $sunPos['altitude'], $sunPos['azimuth']);
                    if ($sep < $minSunSep) {
                        $minSunSep = $sep;
                        $minSunTime = $t;
                        $minSunAlt = $sunPos['altitude'];
                        $minSunAz = $sunPos['azimuth'];
                    }
                }

                if ($location->notify_iss_moon_transit) {
                    $moonPos = \App\Libs\SunCalc::getMoonPosition($date, $qth->lat, $qth->lon);
                    $sep = self::calculateSeparation($sat->el, $sat->az, $moonPos['altitude'], $moonPos['azimuth']);
                    if ($sep < $minMoonSep) {
                        $minMoonSep = $sep;
                        $minMoonTime = $t;
                        $minMoonAlt = $moonPos['altitude'];
                        $minMoonAz = $moonPos['azimuth'];
                    }
                }
            }

            // Fine search
            $fineStep = 0.2 / 86400.0;

            if ($location->notify_iss_sun_transit && $minSunTime !== null && $minSunSep < 3.0) {
                $startFine = max($pass->aos, $minSunTime - (10.0 / 86400.0));
                $endFine = min($pass->los, $minSunTime + (10.0 / 86400.0));

                for ($t = $startFine; $t <= $endFine; $t += $fineStep) {
                    try {
                        $predict->predict_calc($sat, $qth, $t);
                    } catch (\Exception $e) {
                        continue;
                    }

                    $unix = \Predict_Time::daynum2unix($t);
                    $date = new \DateTime("@" . round($unix));
                    $sunPos = \App\Libs\SunCalc::getPosition($date, $qth->lat, $qth->lon);
                    $sep = self::calculateSeparation($sat->el, $sat->az, $sunPos['altitude'], $sunPos['azimuth']);
                    if ($sep < $minSunSep) {
                        $minSunSep = $sep;
                        $minSunTime = $t;
                        $minSunAlt = $sunPos['altitude'];
                        $minSunAz = $sunPos['azimuth'];
                    }
                }
            }

            if ($location->notify_iss_moon_transit && $minMoonTime !== null && $minMoonSep < 3.0) {
                $startFine = max($pass->aos, $minMoonTime - (10.0 / 86400.0));
                $endFine = min($pass->los, $minMoonTime + (10.0 / 86400.0));

                for ($t = $startFine; $t <= $endFine; $t += $fineStep) {
                    try {
                        $predict->predict_calc($sat, $qth, $t);
                    } catch (\Exception $e) {
                        continue;
                    }

                    $unix = \Predict_Time::daynum2unix($t);
                    $date = new \DateTime("@" . round($unix));
                    $moonPos = \App\Libs\SunCalc::getMoonPosition($date, $qth->lat, $qth->lon);
                    $sep = self::calculateSeparation($sat->el, $sat->az, $moonPos['altitude'], $moonPos['azimuth']);
                    if ($sep < $minMoonSep) {
                        $minMoonSep = $sep;
                        $minMoonTime = $t;
                        $minMoonAlt = $moonPos['altitude'];
                        $minMoonAz = $moonPos['azimuth'];
                    }
                }
            }

            $limitDeg = (float) (\App\Models\Setting::where('key', 'conjunction_threshold')->value('value') ?? 0.75);

            $sunPathPoints = [];
            if ($location->notify_iss_sun_transit && $minSunSep <= $limitDeg && $minSunAlt > 0) {
                $startPath = max($pass->aos, $minSunTime - (30.0 / 86400.0));
                $endPath = min($pass->los, $minSunTime + (30.0 / 86400.0));
                $pathStep = 2.0 / 86400.0;

                for ($t = $startPath; $t <= $endPath; $t += $pathStep) {
                    try {
                        $predict->predict_calc($sat, $qth, $t);
                    } catch (\Exception $e) {
                        continue;
                    }

                    $unix = \Predict_Time::daynum2unix($t);
                    $date = new \DateTime("@" . round($unix));
                    $sunPos = \App\Libs\SunCalc::getPosition($date, $qth->lat, $qth->lon);
                    
                    $sunPathPoints[] = [
                        'dx' => round($sat->az  - $sunPos['azimuth'],  4),
                        'dy' => round($sat->el  - $sunPos['altitude'], 4),
                    ];
                }
            }

            $moonPathPoints = [];
            if ($location->notify_iss_moon_transit && $minMoonSep <= $limitDeg && $minMoonAlt > 0) {
                $startPath = max($pass->aos, $minMoonTime - (30.0 / 86400.0));
                $endPath = min($pass->los, $minMoonTime + (30.0 / 86400.0));
                $pathStep = 2.0 / 86400.0;

                for ($t = $startPath; $t <= $endPath; $t += $pathStep) {
                    try {
                        $predict->predict_calc($sat, $qth, $t);
                    } catch (\Exception $e) {
                        continue;
                    }

                    $unix = \Predict_Time::daynum2unix($t);
                    $date = new \DateTime("@" . round($unix));
                    $moonPos = \App\Libs\SunCalc::getMoonPosition($date, $qth->lat, $qth->lon);
                    
                    $moonPathPoints[] = [
                        'dx' => round($sat->az  - $moonPos['azimuth'],  4),
                        'dy' => round($sat->el  - $moonPos['altitude'], 4),
                    ];
                }
            }

            if ($location->notify_iss_sun_transit && $minSunSep <= $limitDeg && $minSunAlt > 0) {
                $unix = \Predict_Time::daynum2unix($minSunTime);
                $date = new \DateTime("@" . round($unix));
                $date->setTimezone(new \DateTimeZone('UTC'));

                $transitRecord = $this->upsertTransit(
                    $location,
                    'sun',
                    $date,
                    $minSunSep,
                    $minSunAlt,
                    $minSunAz,
                    $sunPathPoints,
                    $existingTransits,
                    $matchedTransitIds
                );

                $createdTransits[] = [
                    "id" => $transitRecord->id,
                    "type" => "sun",
                    "time" => $date->format('Y-m-d\TH:i:s\Z'),
                    "separation_degrees" => round($minSunSep, 4),
                    "altitude_degrees" => round($minSunAlt, 2),
                    "azimuth_degrees" => round($minSunAz, 2),
                    "is_exact_transit" => ($minSunSep <= 0.26),
                    "cloud_cover_percent" => $transitRecord->cloud_cover_percent,
                    "notified_at" => $transitRecord->notified_at,
                ];
            }

            if ($location->notify_iss_moon_transit && $minMoonSep <= $limitDeg && $minMoonAlt > 0) {
                $unix = \Predict_Time::daynum2unix($minMoonTime);
                $date = new \DateTime("@" . round($unix));
                $date->setTimezone(new \DateTimeZone('UTC'));

                $transitRecord = $this->upsertTransit(
                    $location,
                    'moon',
                    $date,
                    $minMoonSep,
                    $minMoonAlt,
                    $minMoonAz,
                    $moonPathPoints,
                    $existingTransits,
                    $matchedTransitIds
                );

                $createdTransits[] = [
                    "id" => $transitRecord->id,
                    "type" => "moon",
                    "time" => $date->format('Y-m-d\TH:i:s\Z'),
                    "separation_degrees" => round($minMoonSep, 4),
                    "altitude_degrees" => round($minMoonAlt, 2),
                    "azimuth_degrees" => round($minMoonAz, 2),
                    "is_exact_transit" => ($minMoonSep <= 0.26),
                    "cloud_cover_percent" => $transitRecord->cloud_cover_percent,
                    "notified_at" => $transitRecord->notified_at,
                ];
            }
        }

        // Remove any future transit records that were not matched (pass prediction changed or disappeared)
        $unmatchedIds = $existingTransits->pluck('id')->diff($matchedTransitIds);
        if ($unmatchedIds->isNotEmpty()) {
            ISSTransit::whereIn('id', $unmatchedIds)->delete();
        }

        // Clean up old past records
        ISSTransit::where('location_id', $location->id)->where('time', '<', now()->subHours(2))->delete();

        return $createdTransits;
    }

    private function upsertTransit(
        Location $location,
        string $type,
        \DateTime $date,
        float $sep,
        float $alt,
        float $az,
        ?array $pathPoints,
        $existingTransits,
        array &$matchedTransitIds
    ): ISSTransit {
        $carbonDate = \Carbon\Carbon::instance($date);

        // Find existing record of same type within +/- 15 minutes that hasn't been matched yet
        $existing = $existingTransits->first(function ($t) use ($type, $carbonDate, $matchedTransitIds) {
            return $t->type === $type
                && !in_array($t->id, $matchedTransitIds)
                && abs($carbonDate->diffInMinutes($t->time)) <= 15;
        });

        $cloudCover = $location->getCloudCoverAt($date) ?? ($existing?->cloud_cover_percent);

        if ($existing) {
            $existing->update([
                'time' => $date,
                'separation_degrees' => $sep,
                'altitude_degrees' => $alt,
                'azimuth_degrees' => $az,
                'is_exact_transit' => ($sep <= 0.26),
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
            'is_exact_transit' => ($sep <= 0.26),
            'path_points' => $pathPoints ?: null,
            'cloud_cover_percent' => $cloudCover,
            'notified_at' => null,
        ]);
        $matchedTransitIds[] = $newTransit->id;
        return $newTransit;
    }

    private static function calculateSeparation($el1, $az1, $el2, $az2)
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
