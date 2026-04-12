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
    public $min_night_length_hours = 4;
    public $min_clear_hours = 2;
    public $max_wind_speed = 20.0;
    public $max_cloud_cover = 20;

    protected $rules = [
        'name' => 'required|string|max:255',
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
        'min_night_length_hours' => 'required|integer|min:1|max:24',
        'min_clear_hours' => 'required|integer|min:1|max:24',
        'max_wind_speed' => 'required|numeric|min:0',
        'max_cloud_cover' => 'required|integer|min:0|max:100',
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
            $this->min_night_length_hours = $location->min_night_length_hours;
            $this->min_clear_hours = $location->min_clear_hours;
            $this->max_wind_speed = $location->max_wind_speed;
            $this->max_cloud_cover = $location->max_cloud_cover;
        }
    }

    public function cancelEdit()
    {
        $this->reset(['editingLocationId', 'name', 'town', 'latitude', 'longitude', 'min_night_length_hours', 'min_clear_hours', 'max_wind_speed', 'max_cloud_cover']);
    }

    public function save()
    {
        $this->validate();

        if ($this->editingLocationId) {
            Auth::user()->locations()->find($this->editingLocationId)?->update([
                'name' => $this->name,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'min_night_length_hours' => $this->min_night_length_hours,
                'min_clear_hours' => $this->min_clear_hours,
                'max_wind_speed' => $this->max_wind_speed,
                'max_cloud_cover' => $this->max_cloud_cover,
            ]);
            session()->flash('message', 'Location updated successfully.');
        } else {
            Auth::user()->locations()->create([
                'name' => $this->name,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'min_night_length_hours' => $this->min_night_length_hours,
                'min_clear_hours' => $this->min_clear_hours,
                'max_wind_speed' => $this->max_wind_speed,
                'max_cloud_cover' => $this->max_cloud_cover,
                'is_active' => true,
            ]);
            session()->flash('message', 'Location added successfully.');
        }

        $this->cancelEdit();
    }

    public function delete($id)
    {
        $location = Auth::user()->locations()->find($id);
        if ($location) {
            $location->delete();
            session()->flash('message', 'Location removed.');
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

        return view('livewire.location-manager', [
            'locations' => Auth::user()->locations()->get(),
            'upcomingNights' => $upcomingNights,
        ]);
    }
}
