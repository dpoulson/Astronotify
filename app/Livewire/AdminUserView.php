<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Class AdminUserView
 *
 * Administrative Livewire component for inspecting individual user profiles
 * and their associated observing locations.
 */
class AdminUserView extends Component
{
    public User $user;

    /**
     * Initialize the view with the given user and eager load locations.
     *
     * @param  User  $user  Target user instance.
     */
    public function mount(User $user): void
    {
        $this->user = $user->load('locations');
    }

    /**
     * Render the admin user detail view.
     */
    public function render(): View
    {
        return view('livewire.admin-user-view')->layout('layouts.app');
    }
}
