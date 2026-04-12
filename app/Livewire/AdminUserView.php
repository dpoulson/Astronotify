<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;

class AdminUserView extends Component
{
    public User $user;

    public function mount(User $user)
    {
        $this->user = $user->load('locations');
    }

    public function render()
    {
        return view('livewire.admin-user-view')->layout('layouts.app');
    }
}
