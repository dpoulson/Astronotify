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

        // Clear existing transit records for this location
        ISSTransit::where('location_id', $location->id)->delete();

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

                $createdTransits[] = [
                    "type" => "sun",
                    "time" => $date->format('Y-m-d\TH:i:s\Z'),
                    "separation_degrees" => round($minSunSep, 4),
                    "altitude_degrees" => round($minSunAlt, 2),
                    "azimuth_degrees" => round($minSunAz, 2),
                    "is_exact_transit" => ($minSunSep <= 0.26)
                ];

                ISSTransit::create([
                    'location_id' => $location->id,
                    'type' => 'sun',
                    'time' => $date,
                    'separation_degrees' => $minSunSep,
                    'altitude_degrees' => $minSunAlt,
                    'azimuth_degrees' => $minSunAz,
                    'is_exact_transit' => ($minSunSep <= 0.26),
                    'path_points' => $sunPathPoints ?: null,
                ]);
            }

            if ($location->notify_iss_moon_transit && $minMoonSep <= $limitDeg && $minMoonAlt > 0) {
                $unix = \Predict_Time::daynum2unix($minMoonTime);
                $date = new \DateTime("@" . round($unix));
                $date->setTimezone(new \DateTimeZone('UTC'));

                $createdTransits[] = [
                    "type" => "moon",
                    "time" => $date->format('Y-m-d\TH:i:s\Z'),
                    "separation_degrees" => round($minMoonSep, 4),
                    "altitude_degrees" => round($minMoonAlt, 2),
                    "azimuth_degrees" => round($minMoonAz, 2),
                    "is_exact_transit" => ($minMoonSep <= 0.26)
                ];

                ISSTransit::create([
                    'location_id' => $location->id,
                    'type' => 'moon',
                    'time' => $date,
                    'separation_degrees' => $minMoonSep,
                    'altitude_degrees' => $minMoonAlt,
                    'azimuth_degrees' => $minMoonAz,
                    'is_exact_transit' => ($minMoonSep <= 0.26),
                    'path_points' => $moonPathPoints ?: null,
                ]);
            }
        }

        return $createdTransits;
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
