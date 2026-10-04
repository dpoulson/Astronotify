<?php

namespace App\Livewire;

use App\Models\Location;
use App\Models\User;
use App\Models\WeatherCondition;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Class AdminDashboard
 *
 * Operational dashboard for administrators showing real-time system metrics,
 * 7-day registration charts, API quota consumption, and manual queue/artisan triggers.
 */
class AdminDashboard extends Component
{
    public ?string $sysMessage = null;

    public string $sysMessageType = 'success';

    public int $failedEmailCount = 0;

    /**
     * Manually trigger the weather:fetch artisan command.
     */
    public function triggerWeatherFetch(): void
    {
        try {
            Artisan::call('weather:fetch');
            $this->sysMessage = 'Weather forecast data fetched and processed successfully!';
            $this->sysMessageType = 'success';
            Cache::forget('admin_dashboard_stats_v2');
        } catch (Exception $e) {
            $this->sysMessage = 'Failed to run weather fetch: '.$e->getMessage();
            $this->sysMessageType = 'error';
        }
    }

    /**
     * Manually trigger the weather:iss-transits artisan command.
     */
    public function triggerTransitCalculation(): void
    {
        try {
            Artisan::call('weather:iss-transits');
            $this->sysMessage = 'ISS orbital transits calculated successfully!';
            $this->sysMessageType = 'success';
            Cache::forget('admin_dashboard_stats_v2');
        } catch (Exception $e) {
            $this->sysMessage = 'Failed to run transit calculations: '.$e->getMessage();
            $this->sysMessageType = 'error';
        }
    }

    /**
     * Push all failed queued jobs back to the execution queue.
     */
    public function retryAllFailedEmails(): void
    {
        try {
            Artisan::call('queue:retry', ['id' => 'all']);
            $this->sysMessage = 'All failed jobs have been pushed back to the queue!';
            $this->sysMessageType = 'success';
            Cache::forget('admin_dashboard_stats_v2');
        } catch (Exception $e) {
            $this->sysMessage = 'Failed to retry jobs: '.$e->getMessage();
            $this->sysMessageType = 'error';
        }
    }

    /**
     * Render the administrative dashboard view.
     */
    public function render(): View
    {
        $this->failedEmailCount = DB::table('failed_jobs')->count();

        $stats = Cache::remember('admin_dashboard_stats_v2', 3600, function () {
            $totalConditions = WeatherCondition::count();
            $optimalConditions = WeatherCondition::where('is_optimal', true)->count();
            $uniqueSearchedLocations = WeatherCondition::distinct('location_id')->count('location_id');

            // Group past 7 days for the chart
            $dates = collect(range(6, 0))->map(fn ($days) => today()->subDays($days)->toDateString());

            $userRegistrations = User::where('created_at', '>=', today()->subDays(6))
                ->get()
                ->groupBy(fn ($u) => $u->created_at->toDateString());

            $locationRegistrations = Location::where('created_at', '>=', today()->subDays(6))
                ->get()
                ->groupBy(fn ($l) => $l->created_at->toDateString());

            $chartLabels = [];
            $chartUsers = [];
            $chartLocations = [];

            foreach ($dates as $date) {
                $chartLabels[] = Carbon::parse((string) $date)->format('M d');
                $chartUsers[] = isset($userRegistrations[$date]) ? $userRegistrations[$date]->count() : 0;
                $chartLocations[] = isset($locationRegistrations[$date]) ? $locationRegistrations[$date]->count() : 0;
            }

            $apiCallsToday = (int) DB::table('daily_metrics')->where('key', 'weather_api_calls')->where('date', today())->value('value');
            $apiCallsWeek = (int) DB::table('daily_metrics')->where('key', 'weather_api_calls')->where('date', '>=', today()->subDays(6))->sum('value');
            $apiCallsMonth = (int) DB::table('daily_metrics')->where('key', 'weather_api_calls')->where('date', '>=', today()->subDays(29))->sum('value');

            return [
                'totalUsers' => User::count(),
                'totalLocations' => Location::count(),
                'totalConditions' => $totalConditions,
                'optimalConditions' => $optimalConditions,
                'uniqueSearchedLocations' => $uniqueSearchedLocations,
                'chartLabels' => json_encode($chartLabels),
                'chartUsers' => json_encode($chartUsers),
                'chartLocations' => json_encode($chartLocations),
                'apiCallsToday' => $apiCallsToday,
                'apiCallsWeek' => $apiCallsWeek,
                'apiCallsMonth' => $apiCallsMonth,
            ];
        });

        $stats['failedEmailCount'] = $this->failedEmailCount;

        return view('livewire.admin-dashboard', $stats)->layout('layouts.app');
    }
}
