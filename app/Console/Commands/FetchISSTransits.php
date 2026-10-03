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

        // Run calculations using the reusable ISSTransitCalculator service
        $calculator = new \App\Services\ISSTransitCalculator();
        $userTransits = [];

        foreach ($locations as $loc) {
            $calculator->calculateForLocation($loc);

            // Only notify for transits that haven't been notified yet and don't have overcast cloud cover (>= 90%)
            $alertTransits = \App\Models\ISSTransit::where('location_id', $loc->id)
                ->where('time', '>=', now())
                ->whereNull('notified_at')
                ->where(function ($query) {
                    $query->whereNull('cloud_cover_percent')
                          ->orWhere('cloud_cover_percent', '<', 90);
                })
                ->orderBy('time', 'asc')
                ->get();

            if ($alertTransits->isNotEmpty()) {
                $userId = $loc->user_id;
                $userTransits[$userId]['user'] = [
                    'user_id' => $loc->user_id,
                    'user_email' => $loc->user->email,
                    'user_name' => $loc->user->name
                ];
                $userTransits[$userId]['locations'][$loc->id] = [
                    'location_name' => $loc->name,
                    'transits' => $alertTransits->map(fn($t) => [
                        'id' => $t->id,
                        'type' => $t->type,
                        'time' => $t->time->format('Y-m-d\TH:i:s\Z'),
                        'separation_degrees' => (float) $t->separation_degrees,
                        'altitude_degrees' => (float) $t->altitude_degrees,
                        'azimuth_degrees' => (float) $t->azimuth_degrees,
                        'is_exact_transit' => (bool) $t->is_exact_transit,
                        'cloud_cover_percent' => $t->cloud_cover_percent,
                    ])->toArray()
                ];
                $userTransits[$userId]['transit_ids'] = array_merge(
                    $userTransits[$userId]['transit_ids'] ?? [],
                    $alertTransits->pluck('id')->toArray()
                );
            }
        }

        // Send emails
        if (empty($userTransits)) {
            $this->info("No new upcoming transits with favorable weather detected for any user.");
            return 0;
        }

        $this->info("Queuing summary emails for " . count($userTransits) . " user(s)...");
        foreach ($userTransits as $userId => $data) {
            $userEmail = $data['user']['user_email'];
            $userName = $data['user']['user_name'];
            $transitsList = array_values($data['locations']);
            $user = \App\Models\User::find($userId);

            if (!$user) {
                continue;
            }

            try {
                Log::info("Queuing ISS Transit Summary for User ID: {$userId} ({$userEmail}) with " . count($transitsList) . " locations.");
                Mail::to($userEmail)->queue(new ISSTransitSummary($transitsList, $user));
                \App\Models\ISSTransit::whereIn('id', $data['transit_ids'])->update(['notified_at' => now()]);
                $this->info("ISS Transit Summary queued for {$userName} ({$userEmail})");
            } catch (\Exception $e) {
                Log::error("Failed to queue ISS Transit Summary for User ID: {$userId}: " . $e->getMessage());
                $this->error("Failed to queue email for {$userName}: " . $e->getMessage());
            }
        }

        $this->info("ISS transit check complete.");
        return 0;
    }
}
