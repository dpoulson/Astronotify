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
            $locTransits = $calculator->calculateForLocation($loc);

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
            $user = \App\Models\User::find($userId);

            if (!$user) {
                continue;
            }

            try {
                Log::info("Queuing ISS Transit Summary for User ID: {$userId} ({$userEmail}) with " . count($transitsList) . " locations.");
                Mail::to($userEmail)->queue(new ISSTransitSummary($transitsList, $user));
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
