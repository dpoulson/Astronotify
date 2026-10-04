<?php

namespace App\Livewire;

use App\Models\ISSTransit;
use App\Models\Location;
use App\Models\StargazingSpot;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

/**
 * Class PublicSpotView
 *
 * Livewire component managing the public detail view for a curated stargazing spot,
 * including 4-night cloud forecasts, predicted ISS passes, and 1-click user tracking.
 */
class PublicSpotView extends Component
{
    public string $slug;

    public ?StargazingSpot $spot = null;

    public array $weatherForecast = [];

    public array $upcomingPasses = [];

    public bool $isSavedByUser = false;

    /**
     * Initialize spot view with weather forecast and upcoming passes.
     *
     * @param  string  $slug  Unique slug of the stargazing spot.
     */
    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $this->spot = StargazingSpot::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $this->checkIfSaved();
        $this->loadWeatherForecast();
        $this->loadUpcomingPasses();
    }

    /**
     * Determine whether the currently authenticated user is tracking this spot.
     */
    public function checkIfSaved(): void
    {
        if (Auth::check() && $this->spot) {
            $this->isSavedByUser = Location::where('user_id', Auth::id())
                ->where(function ($q) {
                    $q->where('name', $this->spot->name)
                        ->orWhere(function ($sub) {
                            $sub->whereBetween('latitude', [$this->spot->latitude - 0.01, $this->spot->latitude + 0.01])
                                ->whereBetween('longitude', [$this->spot->longitude - 0.01, $this->spot->longitude + 0.01]);
                        });
                })->exists();
        }
    }

    /**
     * Add the current spot as an active observing location in the user's dashboard.
     */
    public function trackSpotInAccount(): mixed
    {
        if (! Auth::check()) {
            return redirect()->route('register', [
                'name' => $this->spot->name,
                'lat' => $this->spot->latitude,
                'lon' => $this->spot->longitude,
                'elev' => $this->spot->elevation,
                'bortle' => $this->spot->bortle_class,
            ]);
        }

        if ($this->isSavedByUser) {
            session()->flash('message', "You are already tracking {$this->spot->name} in your dashboard.");

            return redirect()->route('dashboard');
        }

        $location = Location::create([
            'user_id' => Auth::id(),
            'name' => $this->spot->name,
            'latitude' => $this->spot->latitude,
            'longitude' => $this->spot->longitude,
            'elevation' => $this->spot->elevation,
            'bortle' => $this->spot->bortle_class,
            'min_night_length_hours' => 6,
            'min_clear_hours' => 3,
            'max_wind_speed' => 25,
            'max_cloud_cover' => 30,
            'is_active' => true,
            'notify_stargazing_alerts' => true,
            'notify_iss_sun_transit' => true,
            'notify_iss_moon_transit' => true,
        ]);

        $location->syncWeatherAndTransits(suppressAlerts: true);

        $this->isSavedByUser = true;
        session()->flash('message', "Added {$this->spot->name} to your locations! Transits and forecasts have been synchronized.");

        return redirect()->route('dashboard');
    }

    /**
     * Load and cache short-range 4-night cloud cover forecasts from Open-Meteo.
     */
    private function loadWeatherForecast(): void
    {
        $cacheKey = "spot_weather_v2_{$this->spot->id}";

        $this->weatherForecast = Cache::remember($cacheKey, 3600, function () {
            try {
                $response = Http::timeout(6)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $this->spot->latitude,
                    'longitude' => $this->spot->longitude,
                    'daily' => 'sunrise,sunset,precipitation_sum',
                    'hourly' => 'cloud_cover,wind_speed_10m,temperature_2m',
                    'timezone' => 'auto',
                    'forecast_days' => 4,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $days = [];

                    if (isset($data['daily']['time'])) {
                        foreach ($data['daily']['time'] as $i => $dateStr) {
                            $sunset = $data['daily']['sunset'][$i] ?? null;
                            $sunrise = $data['daily']['sunrise'][$i] ?? null;

                            // Calculate average nighttime cloud cover (approx between 21:00 and 04:00)
                            $nightClouds = [];
                            if (isset($data['hourly']['time'], $data['hourly']['cloud_cover'])) {
                                foreach ($data['hourly']['time'] as $hIdx => $hTime) {
                                    if (str_starts_with($hTime, $dateStr)) {
                                        $hour = (int) substr($hTime, 11, 2);
                                        if ($hour >= 21 || $hour <= 4) {
                                            $nightClouds[] = (int) $data['hourly']['cloud_cover'][$hIdx];
                                        }
                                    }
                                }
                            }

                            $avgCloud = count($nightClouds) > 0 ? (int) round(array_sum($nightClouds) / count($nightClouds)) : 50;

                            $days[] = [
                                'date' => $dateStr,
                                'date_formatted' => Carbon::parse($dateStr)->format('D, M j'),
                                'sunset' => $sunset ? Carbon::parse($sunset)->format('H:i') : null,
                                'sunrise' => $sunrise ? Carbon::parse($sunrise)->format('H:i') : null,
                                'avg_night_cloud' => $avgCloud,
                                'status' => $avgCloud <= 25 ? 'Optimal' : ($avgCloud <= 60 ? 'Fair' : 'Overcast'),
                                'color' => $avgCloud <= 25 ? 'text-emerald-400 border-emerald-500/30 bg-emerald-950/40' : ($avgCloud <= 60 ? 'text-yellow-400 border-yellow-500/30 bg-yellow-950/40' : 'text-slate-400 border-slate-700 bg-slate-900/60'),
                            ];
                        }
                    }

                    return $days;
                }
            } catch (Exception $e) {
                // Graceful fallback on network timeout
            }

            return [];
        });
    }

    /**
     * Load upcoming modeled ISS passes cached for 6 hours.
     */
    private function loadUpcomingPasses(): void
    {
        $cacheKey = "spot_passes_v2_{$this->spot->id}";

        $this->upcomingPasses = Cache::remember($cacheKey, 21600, function () {
            try {
                // If any existing registered location is near this spot, reuse its transits
                $nearbyLoc = Location::whereBetween('latitude', [$this->spot->latitude - 0.25, $this->spot->latitude + 0.25])
                    ->whereBetween('longitude', [$this->spot->longitude - 0.25, $this->spot->longitude + 0.25])
                    ->first();

                if ($nearbyLoc) {
                    $transits = ISSTransit::where('location_id', $nearbyLoc->id)
                        ->where('time', '>=', now())
                        ->orderBy('time', 'asc')
                        ->limit(3)
                        ->get();

                    return $transits->map(function ($t) {
                        return [
                            'type' => $t->type,
                            'time' => $t->time->format('l, M jS \a\t H:i:s T'),
                            'time_human' => $t->time->diffForHumans(),
                            'separation_degrees' => $t->separation_degrees,
                            'altitude_degrees' => $t->altitude_degrees,
                            'azimuth_degrees' => $t->azimuth_degrees,
                            'is_exact' => $t->is_exact_transit,
                            'token' => $t->public_token,
                        ];
                    })->toArray();
                }
            } catch (Exception $e) {
            }

            return [];
        });
    }

    /**
     * Render the spot detail view.
     */
    public function render(): View
    {
        return view('livewire.public-spot-view', [
            'spot' => $this->spot,
            'nearbySpots' => StargazingSpot::where('is_active', true)
                ->where('id', '!=', $this->spot->id)
                ->where('country', $this->spot->country)
                ->limit(3)
                ->get(),
        ])->layout('layouts.guest');
    }
}
