<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Location;

class ManageNotifications extends Component
{
    public User $user;
    public $preferences = [];
    public $saved = false;

    public function mount(User $user)
    {
        // Guard against unsigned access to ensure the URL cannot be guessed
        $isActualRoute = request()->route() && request()->route()->getName() === 'notifications.manage';
        if ($isActualRoute && !request()->hasValidSignature()) {
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

    public function save()
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

    public function render()
    {
        return view('livewire.manage-notifications')
            ->layout('layouts.guest');
    }
}
