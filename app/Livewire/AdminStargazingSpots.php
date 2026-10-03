<?php

namespace App\Livewire;

use App\Models\StargazingSpot;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class AdminStargazingSpots extends Component
{
    use WithPagination;

    public string $search = '';
    public string $countryFilter = '';
    public string $bortleFilter = '';
    
    public bool $showModal = false;
    public ?int $editingSpotId = null;

    // Form inputs
    public string $name = '';
    public string $slug = '';
    public string $country = 'United Kingdom';
    public string $region = '';
    public string $latitude = '';
    public string $longitude = '';
    public int $elevation = 0;
    public int $bortle_class = 2;
    public string $dark_sky_status = '';
    public string $description = '';
    public bool $is_active = true;

    protected $queryString = [
        'search' => ['except' => ''],
        'countryFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCountryFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->editingSpotId = null;
        $this->showModal = true;
    }

    public function editSpot(int $id)
    {
        $this->resetValidation();
        $spot = StargazingSpot::findOrFail($id);
        
        $this->editingSpotId = $spot->id;
        $this->name = $spot->name;
        $this->slug = $spot->slug;
        $this->country = $spot->country;
        $this->region = $spot->region ?? '';
        $this->latitude = (string) $spot->latitude;
        $this->longitude = (string) $spot->longitude;
        $this->elevation = (int) $spot->elevation;
        $this->bortle_class = (int) $spot->bortle_class;
        $this->dark_sky_status = $spot->dark_sky_status ?? '';
        $this->description = $spot->description ?? '';
        $this->is_active = (bool) $spot->is_active;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:stargazing_spots,slug,' . ($this->editingSpotId ?? 'NULL') . ',id',
            'country' => 'required|string|max:100',
            'region' => 'nullable|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'elevation' => 'required|integer|min:-500|max:9000',
            'bortle_class' => 'required|integer|between:1,9',
            'dark_sky_status' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'is_active' => 'boolean',
        ]);

        $slug = !empty($this->slug) ? Str::slug($this->slug) : Str::slug($this->name);

        $payload = [
            'name' => $this->name,
            'slug' => $slug,
            'country' => $this->country,
            'region' => $this->region,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'elevation' => $this->elevation,
            'bortle_class' => $this->bortle_class,
            'dark_sky_status' => $this->dark_sky_status,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        if ($this->editingSpotId) {
            $spot = StargazingSpot::findOrFail($this->editingSpotId);
            $spot->update($payload);
            session()->flash('message', "Updated spot '{$spot->name}'.");
        } else {
            $spot = StargazingSpot::create($payload);
            session()->flash('message', "Created spot '{$spot->name}'.");
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActive(int $id)
    {
        $spot = StargazingSpot::findOrFail($id);
        $spot->is_active = !$spot->is_active;
        $spot->save();
        session()->flash('message', "Toggled active state for '{$spot->name}'.");
    }

    public function deleteSpot(int $id)
    {
        $spot = StargazingSpot::findOrFail($id);
        $name = $spot->name;
        $spot->delete();
        session()->flash('message', "Deleted spot '{$name}'.");
    }

    private function resetForm()
    {
        $this->name = '';
        $this->slug = '';
        $this->country = 'United Kingdom';
        $this->region = '';
        $this->latitude = '';
        $this->longitude = '';
        $this->elevation = 0;
        $this->bortle_class = 2;
        $this->dark_sky_status = '';
        $this->description = '';
        $this->is_active = true;
    }

    public function render()
    {
        $query = StargazingSpot::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('country', 'like', '%' . $this->search . '%')
                  ->orWhere('region', 'like', '%' . $this->search . '%')
                  ->orWhere('dark_sky_status', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->countryFilter)) {
            $query->where('country', $this->countryFilter);
        }

        if (!empty($this->bortleFilter)) {
            $query->where('bortle_class', (int) $this->bortleFilter);
        }

        $spots = $query->orderBy('country')->orderBy('name')->paginate(15);
        $countries = StargazingSpot::distinct()->orderBy('country')->pluck('country');

        return view('livewire.admin-stargazing-spots', [
            'spots' => $spots,
            'countries' => $countries,
            'totalSpots' => StargazingSpot::count(),
            'activeSpots' => StargazingSpot::where('is_active', true)->count(),
        ])->layout('layouts.app');
    }
}
