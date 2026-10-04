<?php

namespace App\Livewire\Traits;

use Illuminate\Support\Facades\Gate;

trait AuthorizesAdminAccess
{
    /**
     * Authorize that the current authenticated user has administrative privileges.
     * Invoked automatically by Livewire on component initialization and requests.
     */
    public function bootAuthorizesAdminAccess(): void
    {
        Gate::authorize('admin');
    }
}
