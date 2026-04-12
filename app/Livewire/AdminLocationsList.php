<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Location;

class AdminLocationsList extends Component
{
    use WithPagination;

    public function deleteLocation($id)
    {
        $location = Location::find($id);
        if ($location) {
            $location->delete();
        }
    }

    public function render()
    {
        return view('livewire.admin-locations-list', [
            'locations' => Location::with('user')->paginate(15)
        ])->layout('layouts.app');
    }
}
