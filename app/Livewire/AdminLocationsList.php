<?php

namespace App\Livewire;

use App\Livewire\Traits\AuthorizesAdminAccess;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Class AdminLocationsList
 *
 * Administrative Livewire component for listing and managing all user-created
 * observing locations across the application.
 */
class AdminLocationsList extends Component
{
    use AuthorizesAdminAccess;
    use WithPagination;

    /**
     * Delete an observing location by its ID.
     *
     * @param  int|string  $id  Location ID.
     */
    public function deleteLocation(int|string $id): void
    {
        $location = Location::find($id);
        if ($location) {
            $location->delete();
        }
    }

    /**
     * Render the admin locations directory view.
     */
    public function render(): View
    {
        return view('livewire.admin-locations-list', [
            'locations' => Location::with('user')->paginate(15),
        ])->layout('layouts.app');
    }
}
