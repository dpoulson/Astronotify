<?php

namespace App\Livewire;

use App\Livewire\Traits\AuthorizesAdminAccess;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Class AdminUsersList
 *
 * Administrative Livewire component for browsing registered users,
 * location counts, and administrative deletion.
 */
class AdminUsersList extends Component
{
    use AuthorizesAdminAccess;
    use WithPagination;

    /**
     * Delete a user account and associated location records.
     *
     * @param  int|string  $id  User ID.
     */
    public function deleteUser(int|string $id): void
    {
        // Prevent deleting own account or root admin from admin user list
        if ((int) $id === (int) Auth::id() || (int) $id === 1) {
            return;
        }

        $user = User::find($id);
        if ($user) {
            $user->locations()->delete();
            $user->delete();
        }
    }

    /**
     * Render the admin users directory view.
     */
    public function render(): View
    {
        return view('livewire.admin-users-list', [
            'users' => User::withCount('locations')->paginate(15),
        ])->layout('layouts.app');
    }
}
