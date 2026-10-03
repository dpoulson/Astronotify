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
    public string $view = 'grid';

    protected $queryString = [
        'search' => ['except' => ''],
        'country' => ['except' => ''],
        'bortle' => ['except' => ''],
        'view' => ['except' => 'grid'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->dispatch('map-spots-updated', spots: $this->getMapSpots());
    }

    public function updatingCountry()
    {
        $this->resetPage();
    }

    public function updatedCountry()
    {
        $this->dispatch('map-spots-updated', spots: $this->getMapSpots());
    }

    public function updatingBortle()
    {
        $this->resetPage();
    }

    public function updatedBortle()
    {
        $this->dispatch('map-spots-updated', spots: $this->getMapSpots());
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->country = '';
        $this->bortle = '';
        $this->resetPage();
        $this->dispatch('map-spots-updated', spots: $this->getMapSpots());
    }

    public function setView(string $view): void
    {
        $this->view = in_array($view, ['grid', 'map']) ? $view : 'grid';
    }

    protected function getFilteredQuery()
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

        return $query;
    }

    public function getMapSpots()
    {
        return $this->getFilteredQuery()
            ->orderBy('bortle_class', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'slug' => $s->slug,
                'country' => $s->country,
                'region' => $s->region,
                'lat' => (float) $s->latitude,
                'lng' => (float) $s->longitude,
                'elevation' => $s->elevation,
                'bortle_class' => $s->bortle_class,
                'bortle_color' => $s->bortle_color,
                'bortle_desc' => $s->bortle_description,
                'dark_sky_status' => $s->dark_sky_status,
                'description' => \Illuminate\Support\Str::limit($s->description, 140),
                'url' => route('spots.show', $s->slug),
            ]);
    }

    public function render()
    {
        $query = $this->getFilteredQuery();
        $mapSpots = $this->getMapSpots();

        $spots = (clone $query)->orderBy('bortle_class', 'asc')
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
            'mapSpots' => $mapSpots,
            'countries' => $countries,
            'totalSpots' => $totalSpots,
            'bortle1Count' => $bortle1Count,
            'bortle2Count' => $bortle2Count,
        ])->layout('layouts.guest');
    }
}
