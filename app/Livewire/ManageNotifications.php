<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Class ManageNotifications
 *
 * Publicly accessible signed Livewire component allowing users to manage per-location
 * email alert toggles (stargazing, solar transits, lunar transits) without logging in.
 */
class ManageNotifications extends Component
{
    public User $user;

    public array $preferences = [];

    public bool $saved = false;

    /**
     * Verify signed URL and initialize per-location notification preferences.
     *
     * @param  User  $user  Target user resolved from signed URL route binding.
     */
    public function mount(User $user): void
    {
        // Guard against unsigned access to ensure the URL cannot be guessed
        $isActualRoute = request()->route() && request()->route()->getName() === 'notifications.manage';
        if ($isActualRoute && ! request()->hasValidSignature()) {
            abort(401, 'This unsubscribe link is invalid or has expired.');
        }

        $this->user = $user;

        foreach ($user->locations as $location) {
            $this->preferences[$location->id] = [
                'name' => $location->name,
                'notify_stargazing_alerts' => (bool) $location->notify_stargazing_alerts,
                'notify_iss_sun_transit' => (bool) $location->notify_iss_sun_transit,
                'notify_iss_moon_transit' => (bool) $location->notify_iss_moon_transit,
            ];
        }
    }

    /**
     * Persist updated notification preferences for all user locations.
     */
    public function save(): void
    {
        foreach ($this->preferences as $locationId => $prefs) {
            $location = $this->user->locations()->find($locationId);
            if ($location) {
                $location->update([
                    'notify_stargazing_alerts' => (bool) $prefs['notify_stargazing_alerts'],
                    'notify_iss_sun_transit' => (bool) $prefs['notify_iss_sun_transit'],
                    'notify_iss_moon_transit' => (bool) $prefs['notify_iss_moon_transit'],
                ]);
            }
        }

        $this->saved = true;
    }

    /**
     * Render the guest notifications management template.
     */
    public function render(): View
    {
        return view('livewire.manage-notifications')
            ->layout('layouts.guest');
    }
}
