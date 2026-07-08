<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;

class LocationManager extends Component
{
    public $editingLocationId = null;

    public $name = '';
    public $town = '';
    public $latitude = '';
    public $longitude = '';
    public $elevation = 0;
    public $min_night_length_hours = 4;
    public $min_clear_hours = 2;
    public $max_wind_speed = 20.0;
    public $max_cloud_cover = 20;
    public $notify_iss_sun_transit = false;
    public $notify_iss_moon_transit = false;
    public $notify_stargazing_alerts = true;
    public $loadedPasses = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
        'elevation' => 'required|integer|min:-100|max:10000',
        'min_night_length_hours' => 'required|integer|min:1|max:24',
        'min_clear_hours' => 'required|integer|min:1|max:24',
        'max_wind_speed' => 'required|numeric|min:0',
        'max_cloud_cover' => 'required|integer|min:0|max:100',
        'notify_iss_sun_transit' => 'boolean',
        'notify_iss_moon_transit' => 'boolean',
        'notify_stargazing_alerts' => 'boolean',
    ];

    public function updatedTown($value)
    {
        if (strlen($value) > 2) {
            $response = \Illuminate\Support\Facades\Http::withUserAgent('Astronotify/1.0')->get('https://nominatim.openstreetmap.org/search', [
                'q' => $value,
                'format' => 'json',
                'limit' => 1,
            ]);

            if ($response->successful() && !empty($response->json())) {
                $result = $response->json()[0];
                $this->latitude = round((float) $result['lat'], 5);
                $this->longitude = round((float) $result['lon'], 5);
            }
        }
    }
    public function edit($id)
    {
        $location = Auth::user()->locations()->find($id);
        if ($location) {
            $this->editingLocationId = $location->id;
            $this->name = $location->name;
            $this->latitude = $location->latitude;
            $this->longitude = $location->longitude;
            $this->elevation = $location->elevation;
            $this->min_night_length_hours = $location->min_night_length_hours;
            $this->min_clear_hours = $location->min_clear_hours;
            $this->max_wind_speed = $location->max_wind_speed;
            $this->max_cloud_cover = $location->max_cloud_cover;
            $this->notify_iss_sun_transit = (bool) $location->notify_iss_sun_transit;
            $this->notify_iss_moon_transit = (bool) $location->notify_iss_moon_transit;
            $this->notify_stargazing_alerts = (bool) $location->notify_stargazing_alerts;
            $this->dispatch('open-form');
        }
    }

    public function cancelEdit()
    {
        $this->reset(['editingLocationId', 'name', 'town', 'latitude', 'longitude', 'elevation', 'min_night_length_hours', 'min_clear_hours', 'max_wind_speed', 'max_cloud_cover', 'notify_iss_sun_transit', 'notify_iss_moon_transit', 'notify_stargazing_alerts']);
        if (Auth::user()->locations()->count() > 0) {
            $this->dispatch('close-form');
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->editingLocationId) {
            $location = Auth::user()->locations()->find($this->editingLocationId);
            if ($location) {
                $location->update([
                    'name' => $this->name,
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude,
                    'elevation' => $this->elevation,
                    'min_night_length_hours' => $this->min_night_length_hours,
                    'min_clear_hours' => $this->min_clear_hours,
                    'max_wind_speed' => $this->max_wind_speed,
                    'max_cloud_cover' => $this->max_cloud_cover,
                    'notify_iss_sun_transit' => $this->notify_iss_sun_transit,
                    'notify_iss_moon_transit' => $this->notify_iss_moon_transit,
                    'notify_stargazing_alerts' => $this->notify_stargazing_alerts,
                ]);
                $location->reevaluateConditions();

                // Recalculate ISS transits immediately
                $calculator = new \App\Services\ISSTransitCalculator();
                $calculator->calculateForLocation($location);
            }
            session()->flash('message', 'Location updated successfully.');
        } else {
            $location = Auth::user()->locations()->create([
                'name' => $this->name,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'elevation' => $this->elevation,
                'min_night_length_hours' => $this->min_night_length_hours,
                'min_clear_hours' => $this->min_clear_hours,
                'max_wind_speed' => $this->max_wind_speed,
                'max_cloud_cover' => $this->max_cloud_cover,
                'notify_iss_sun_transit' => $this->notify_iss_sun_transit,
                'notify_iss_moon_transit' => $this->notify_iss_moon_transit,
                'notify_stargazing_alerts' => $this->notify_stargazing_alerts,
                'is_active' => true,
            ]);

            // Recalculate ISS transits immediately
            $calculator = new \App\Services\ISSTransitCalculator();
            $calculator->calculateForLocation($location);

            session()->flash('message', 'Location added successfully.');
        }

        $this->cancelEdit();
        if (Auth::user()->locations()->count() > 0) {
            $this->dispatch('close-form');
        }
    }

    public function delete($id)
    {
        $location = Auth::user()->locations()->find($id);
        if ($location) {
            $location->delete();
            session()->flash('message', 'Location removed.');
        }
    }
    public function loadPasses($locationId)
    {
        if (isset($this->loadedPasses[$locationId])) {
            return;
        }

        $location = Auth::user()->locations()->find($locationId);
        if (!$location) {
            return;
        }

        $tleBody = \Illuminate\Support\Facades\Cache::remember('iss_tle_data', 14400, function () {
            $response = \Illuminate\Support\Facades\Http::get('https://celestrak.org/NORAD/elements/gp.php?CATNR=25544&FORMAT=tle');
            return $response->successful() ? $response->body() : null;
        });

        if (!$tleBody) {
            $this->loadedPasses[$locationId] = [];
            return;
        }

        $lines = explode("\n", trim($tleBody));
        if (count($lines) < 3) {
            $this->loadedPasses[$locationId] = [];
            return;
        }

        $tleName = trim($lines[0]);
        $tleLine1 = trim($lines[1]);
        $tleLine2 = trim($lines[2]);

        $tle = new \Predict_TLE($tleName, $tleLine1, $tleLine2);
        $sat = new \Predict_Sat($tle);
        $predict = new \Predict();

        $startJD = \Predict_Time::get_current_daynum();
        $qth = new \Predict_QTH();
        $qth->lat = (float) $location->latitude;
        $qth->lon = (float) $location->longitude;
        $qth->alt = (float) ($location->elevation ?? 0.0);

        try {
            $passes = $predict->get_passes($sat, $qth, $startJD, 7);
        } catch (\Exception $e) {
            $this->loadedPasses[$locationId] = [];
            return;
        }

        $formattedPasses = [];
        $limit = 8;
        foreach (array_slice($passes, 0, $limit) as $pass) {
            $aosUnix = \Predict_Time::daynum2unix($pass->aos);
            $losUnix = \Predict_Time::daynum2unix($pass->los);

            $formattedPasses[] = [
                'date' => \Carbon\Carbon::parse("@" . round($aosUnix))->timezone(config('app.timezone', 'UTC'))->format('D M jS'),
                'aos' => \Carbon\Carbon::parse("@" . round($aosUnix))->timezone(config('app.timezone', 'UTC'))->format('H:i:s'),
                'los' => \Carbon\Carbon::parse("@" . round($losUnix))->timezone(config('app.timezone', 'UTC'))->format('H:i:s'),
                'duration' => round(($losUnix - $aosUnix) / 60, 1),
                'max_el' => round($pass->max_el, 0),
            ];
        }

        $this->loadedPasses[$locationId] = $formattedPasses;
    }

    public function sendTestEmail($id)
    {
        $location = Auth::user()->locations()->find($id);
        if ($location) {
            try {
                \Illuminate\Support\Facades\Mail::to(Auth::user()->email)
                    ->send(new \App\Mail\LocationTestMail($location, Auth::user()->name));
                
                session()->flash('message', 'Test notification sent successfully to ' . Auth::user()->email);
            } catch (\Exception $e) {
                session()->flash('error', 'Failed to send test email: ' . $e->getMessage());
            }
        }
    }

    public function render()
    {
        $upcomingNights = \App\Models\WeatherCondition::with('location')
            ->whereHas('location', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->where('is_optimal', true)
            ->where('date', '>=', today()->toDateString())
            ->orderBy('date', 'asc')
            ->get();

        $upcomingTransits = \App\Models\ISSTransit::with('location')
            ->whereHas('location', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->where('time', '>=', now())
            ->orderBy('time', 'asc')
            ->get();

        return view('livewire.location-manager', [
            'locations' => Auth::user()->locations()->with(['conditions' => function ($q) {
                $q->where('date', '>=', today()->toDateString())->orderBy('date', 'asc');
            }])->get(),
            'upcomingNights' => $upcomingNights,
            'upcomingTransits' => $upcomingTransits,
        ]);
    }
}
