<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Location;
use App\Models\WeatherCondition;
use Illuminate\Support\Facades\DB;

class AdminDashboard extends Component
{
    public function render()
    {
        $totalConditions = WeatherCondition::count();
        $optimalConditions = WeatherCondition::where('is_optimal', true)->count();
        $uniqueSearchedLocations = WeatherCondition::distinct('location_id')->count('location_id');

        // Group past 7 days for the chart
        $dates = collect(range(6, 0))->map(fn ($days) => today()->subDays($days)->toDateString());
        
        $userRegistrations = User::where('created_at', '>=', today()->subDays(6))->get()->groupBy(fn($u) => $u->created_at->toDateString());
        $locationRegistrations = Location::where('created_at', '>=', today()->subDays(6))->get()->groupBy(fn($l) => $l->created_at->toDateString());

        $chartLabels = [];
        $chartUsers = [];
        $chartLocations = [];

        foreach ($dates as $date) {
            $chartLabels[] = \Carbon\Carbon::parse($date)->format('M d');
            $chartUsers[] = isset($userRegistrations[$date]) ? $userRegistrations[$date]->count() : 0;
            $chartLocations[] = isset($locationRegistrations[$date]) ? $locationRegistrations[$date]->count() : 0;
        }

        return view('livewire.admin-dashboard', [
            'totalUsers' => User::count(),
            'totalLocations' => Location::count(),
            'totalConditions' => $totalConditions,
            'optimalConditions' => $optimalConditions,
            'uniqueSearchedLocations' => $uniqueSearchedLocations,
            'chartLabels' => json_encode($chartLabels),
            'chartUsers' => json_encode($chartUsers),
            'chartLocations' => json_encode($chartLocations),
        ])->layout('layouts.app');
    }
}
