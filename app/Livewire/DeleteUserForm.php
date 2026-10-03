<?php

namespace App\Livewire;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Contracts\DeletesUsers;
use Laravel\Jetstream\Http\Livewire\DeleteUserForm as JetstreamDeleteUserForm;

class DeleteUserForm extends JetstreamDeleteUserForm
{
    /**
     * Delete the current user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Jetstream\Contracts\DeletesUsers  $deleter
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $auth
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function deleteUser(Request $request, DeletesUsers $deleter, StatefulGuard $auth)
    {
        $this->resetErrorBag();

        $user = Auth::user();

        // Require password check only if a password exists on the user record.
        // Google OAuth users (where password is null) can delete without password.
        if (! empty($user->password)) {
            if (! Hash::check($this->password, $user->password)) {
                throw ValidationException::withMessages([
                    'password' => [__('This password does not match our records.')],
                ]);
            }
        }

        $deleter->delete($user->fresh());

        $auth->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect(config('fortify.redirects.logout') ?? '/');
    }
}
