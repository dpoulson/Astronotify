<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Location;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\ISSTransitSummary;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Support\Facades\Log;

#[Signature('weather:iss-transits')]
#[Description('Calculates ISS solar and lunar transits/conjunctions for active stargazing spots in pure PHP.')]
class FetchISSTransits extends Command
{
    public function handle()
    {
        $this->info("Fetching active locations for ISS transit predictions...");
        
        $locations = Location::query()
            ->with('user')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('notify_iss_sun_transit', true)
                      ->orWhere('notify_iss_moon_transit', true);
            })
            ->get();

        if ($locations->isEmpty()) {
            $this->info("No active locations configured for ISS transits.");
            return 0;
        }

        $this->info("Found " . $locations->count() . " active location(s) to check.");

        // 1. Fetch TLE from CelesTrak (ISS NORAD ID 25544)
        $this->info("Fetching latest TLE for ISS (NORAD 25544) from CelesTrak...");
        $response = Http::get('https://celestrak.org/NORAD/elements/gp.php?CATNR=25544&FORMAT=tle');
        if ($response->failed()) {
            $this->error("Failed to download TLE data from CelesTrak.");
            Log::error("ISS Transit check: Failed to download TLE from CelesTrak.");
            return 1;
        }

        $lines = explode("\n", trim($response->body()));
        if (count($lines) < 3) {
            $this->error("Invalid TLE response from CelesTrak.");
            Log::error("ISS Transit check: Invalid TLE response format.");
            return 1;
        }

        $tleName = trim($lines[0]);
        $tleLine1 = trim($lines[1]);
        $tleLine2 = trim($lines[2]);

        // 2. Set up Predict include path and autoloader
        set_include_path(get_include_path() . PATH_SEPARATOR . base_path('app/Libs'));
        spl_autoload_register(function ($class) {
            if (strpos($class, 'Predict') === 0) {
                $file = base_path('app/Libs/' . str_replace('_', '/', $class) . '.php');
                if (file_exists($file)) {
                    require_once $file;
                }
            }
        });

        $tle = new \Predict_TLE($tleName, $tleLine1, $tleLine2);
        $sat = new \Predict_Sat($tle);
        $predict = new \Predict();

        $forecastDays = (int) (\App\Models\Setting::where('key', 'forecast_days')->value('value') ?? 7);
        $startJD = \Predict_Time::get_current_daynum();

        $this->info("Running orbital propagation and transit calculations for {$forecastDays} days...");

        $userTransits = [];

        foreach ($locations as $loc) {
            $qth = new \Predict_QTH();
            $qth->lat = (float) $loc->latitude;
            $qth->lon = (float) $loc->longitude;
            $qth->alt = (float) ($loc->elevation ?? 0.0);

            // Find passes above 10 deg elevation
            try {
                $passes = $predict->get_passes($sat, $qth, $startJD, $forecastDays);
            } catch (\Exception $e) {
                Log::error("Failed to calculate passes for location {$loc->name}: " . $e->getMessage());
                continue;
            }

            $locTransits = [];

            foreach ($passes as $pass) {
                // Duration of the pass in Julian days
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

                // Step 1: Coarse search every 10 seconds
                $coarseStep = 10.0 / 86400.0;
                for ($t = $pass->aos; $t <= $pass->los; $t += $coarseStep) {
                    try {
                        $predict->predict_calc($sat, $qth, $t);
                    } catch (\Exception $e) {
                        continue;
                    }

                    $unix = \Predict_Time::daynum2unix($t);
                    $date = new \DateTime("@" . round($unix));

                    if ($loc->notify_iss_sun_transit) {
                        $sunPos = \App\Libs\SunCalc::getPosition($date, $qth->lat, $qth->lon);
                        $sep = self::calculateSeparation($sat->el, $sat->az, $sunPos['altitude'], $sunPos['azimuth']);
                        if ($sep < $minSunSep) {
                            $minSunSep = $sep;
                            $minSunTime = $t;
                            $minSunAlt = $sunPos['altitude'];
                            $minSunAz = $sunPos['azimuth'];
                        }
                    }

                    if ($loc->notify_iss_moon_transit) {
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

                // Step 2: Fine search around the coarse minimum (within +/- 10 seconds, step of 0.2 seconds)
                $fineStep = 0.2 / 86400.0;

                if ($loc->notify_iss_sun_transit && $minSunTime !== null && $minSunSep < 3.0) {
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

                if ($loc->notify_iss_moon_transit && $minMoonTime !== null && $minMoonSep < 3.0) {
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

                // Check limits (separation <= 0.75 degrees)
                $limitDeg = 0.75;

                if ($loc->notify_iss_sun_transit && $minSunSep <= $limitDeg && $minSunAlt > 0) {
                    $unix = \Predict_Time::daynum2unix($minSunTime);
                    $date = new \DateTime("@" . round($unix));
                    $date->setTimezone(new \DateTimeZone('UTC'));

                    $locTransits[] = [
                        "type" => "sun",
                        "time" => $date->format('Y-m-d\TH:i:s\Z'),
                        "separation_degrees" => round($minSunSep, 4),
                        "altitude_degrees" => round($minSunAlt, 2),
                        "azimuth_degrees" => round($minSunAz, 2),
                        "is_exact_transit" => ($minSunSep <= 0.26)
                    ];
                }

                if ($loc->notify_iss_moon_transit && $minMoonSep <= $limitDeg && $minMoonAlt > 0) {
                    $unix = \Predict_Time::daynum2unix($minMoonTime);
                    $date = new \DateTime("@" . round($unix));
                    $date->setTimezone(new \DateTimeZone('UTC'));

                    $locTransits[] = [
                        "type" => "moon",
                        "time" => $date->format('Y-m-d\TH:i:s\Z'),
                        "separation_degrees" => round($minMoonSep, 4),
                        "altitude_degrees" => round($minMoonAlt, 2),
                        "azimuth_degrees" => round($minMoonAz, 2),
                        "is_exact_transit" => ($minMoonSep <= 0.26)
                    ];
                }
            }

            if (!empty($locTransits)) {
                $userId = $loc->user_id;
                $userTransits[$userId]['user'] = [
                    'user_id' => $loc->user_id,
                    'user_email' => $loc->user->email,
                    'user_name' => $loc->user->name
                ];
                $userTransits[$userId]['locations'][$loc->id] = [
                    'location_name' => $loc->name,
                    'transits' => $locTransits
                ];
            }
        }

        // Send emails
        if (empty($userTransits)) {
            $this->info("No upcoming transits/conjunctions detected for any user in this window.");
            return 0;
        }

        $this->info("Queuing summary emails for " . count($userTransits) . " user(s)...");
        foreach ($userTransits as $userId => $data) {
            $userEmail = $data['user']['user_email'];
            $userName = $data['user']['user_name'];
            $transitsList = array_values($data['locations']);

            try {
                Log::info("Queuing ISS Transit Summary for User ID: {$userId} ({$userEmail}) with " . count($transitsList) . " locations.");
                Mail::to($userEmail)->queue(new ISSTransitSummary($transitsList, $userName));
                $this->info("ISS Transit Summary queued for {$userName} ({$userEmail})");
            } catch (\Exception $e) {
                Log::error("Failed to queue ISS Transit Summary for User ID: {$userId}: " . $e->getMessage());
                $this->error("Failed to queue email for {$userName}: " . $e->getMessage());
            }
        }

        $this->info("ISS transit check complete.");
        return 0;
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
