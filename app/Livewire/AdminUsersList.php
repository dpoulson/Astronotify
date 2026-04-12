<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;

class AdminUsersList extends Component
{
    use WithPagination;

    public function deleteUser($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->locations()->delete();
            $user->delete();
        }
    }

    public function render()
    {
        return view('livewire.admin-users-list', [
            'users' => User::withCount('locations')->paginate(15)
        ])->layout('layouts.app');
    }
}
