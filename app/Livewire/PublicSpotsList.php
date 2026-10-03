<?php

namespace App\Livewire;

use App\Models\StargazingSpot;
use Livewire\Component;
use Livewire\WithPagination;

class PublicSpotsList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $country = '';
    public string $bortle = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'country' => ['except' => ''],
        'bortle' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCountry()
    {
        $this->resetPage();
    }

    public function updatingBortle()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = StargazingSpot::query()->where('is_active', true);

        if (!empty($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('region', 'like', $term)
                  ->orWhere('country', 'like', $term)
                  ->orWhere('dark_sky_status', 'like', $term)
                  ->orWhere('description', 'like', $term);
            });
        }

        if (!empty($this->country)) {
            $query->where('country', $this->country);
        }

        if (!empty($this->bortle)) {
            $query->where('bortle_class', '<=', (int) $this->bortle);
        }

        $spots = $query->orderBy('bortle_class', 'asc')
            ->orderBy('country', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(12);

        $countries = StargazingSpot::where('is_active', true)
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        $totalSpots = StargazingSpot::where('is_active', true)->count();
        $bortle1Count = StargazingSpot::where('is_active', true)->where('bortle_class', 1)->count();
        $bortle2Count = StargazingSpot::where('is_active', true)->where('bortle_class', 2)->count();

        return view('livewire.public-spots-list', [
            'spots' => $spots,
            'countries' => $countries,
            'totalSpots' => $totalSpots,
            'bortle1Count' => $bortle1Count,
            'bortle2Count' => $bortle2Count,
        ])->layout('layouts.guest');
    }
}
