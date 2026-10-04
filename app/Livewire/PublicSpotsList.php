<?php

namespace App\Livewire;

use App\Models\StargazingSpot;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Class PublicSpotsList
 *
 * Livewire component managing the public directory of curated dark-sky stargazing spots,
 * providing real-time text/country/Bortle filtering, pagination, and Leaflet map synchronization.
 */
class PublicSpotsList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $country = '';

    public string $bortle = '';

    public string $view = 'grid';

    /**
     * Query string persistence bindings.
     *
     * @var array<string, array<string, string>>
     */
    protected $queryString = [
        'search' => ['except' => ''],
        'country' => ['except' => ''],
        'bortle' => ['except' => ''],
        'view' => ['except' => 'grid'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->dispatch('map-spots-updated', spots: $this->getMapSpots());
    }

    public function updatingCountry(): void
    {
        $this->resetPage();
    }

    public function updatedCountry(): void
    {
        $this->dispatch('map-spots-updated', spots: $this->getMapSpots());
    }

    public function updatingBortle(): void
    {
        $this->resetPage();
    }

    public function updatedBortle(): void
    {
        $this->dispatch('map-spots-updated', spots: $this->getMapSpots());
    }

    /**
     * Clear all filters and reset back to initial view.
     */
    public function resetFilters(): void
    {
        $this->search = '';
        $this->country = '';
        $this->bortle = '';
        $this->resetPage();
        $this->dispatch('map-spots-updated', spots: $this->getMapSpots());
    }

    /**
     * Switch directory presentation between 'grid' and 'map'.
     *
     * @param  string  $view  Selected view mode ('grid' or 'map').
     */
    public function setView(string $view): void
    {
        $this->view = in_array($view, ['grid', 'map'], true) ? $view : 'grid';
    }

    /**
     * Build the filtered Eloquent query based on active search, country, and Bortle class.
     *
     * @return Builder<StargazingSpot>
     */
    protected function getFilteredQuery(): Builder
    {
        $query = StargazingSpot::query()->where('is_active', true);

        if (! empty($this->search)) {
            $term = '%'.trim($this->search).'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('region', 'like', $term)
                    ->orWhere('country', 'like', $term)
                    ->orWhere('dark_sky_status', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        if (! empty($this->country)) {
            $query->where('country', $this->country);
        }

        if (! empty($this->bortle)) {
            $query->where('bortle_class', '<=', (int) $this->bortle);
        }

        return $query;
    }

    /**
     * Get lightweight collection of spots formatted for the interactive Leaflet map.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getMapSpots(): Collection
    {
        return $this->getFilteredQuery()
            ->orderBy('bortle_class', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->map(fn (StargazingSpot $s) => [
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
                'description' => Str::limit($s->description, 140),
                'url' => route('spots.show', $s->slug),
            ]);
    }

    /**
     * Render the public spots list view.
     */
    public function render(): View
    {
        $query = $this->getFilteredQuery();
        $mapSpots = $this->getMapSpots();

        $spots = (clone $query)->orderBy('bortle_class', 'asc')
            ->orderBy('country', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(12);

        $counts = StargazingSpot::where('is_active', true)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN bortle_class = 1 THEN 1 ELSE 0 END) as b1, SUM(CASE WHEN bortle_class = 2 THEN 1 ELSE 0 END) as b2')
            ->first();

        $countries = StargazingSpot::where('is_active', true)
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        return view('livewire.public-spots-list', [
            'spots' => $spots,
            'mapSpots' => $mapSpots,
            'countries' => $countries,
            'totalSpots' => (int) ($counts->total ?? 0),
            'bortle1Count' => (int) ($counts->b1 ?? 0),
            'bortle2Count' => (int) ($counts->b2 ?? 0),
        ])->layout('layouts.guest');
    }
}
