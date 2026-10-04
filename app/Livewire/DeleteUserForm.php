<?php

namespace App\Livewire;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Contracts\DeletesUsers;
use Laravel\Jetstream\Http\Livewire\DeleteUserForm as JetstreamDeleteUserForm;

/**
 * Class DeleteUserForm
 *
 * Custom Jetstream account deletion form allowing OAuth users without passwords
 * to delete their accounts cleanly while enforcing password verification for standard users.
 */
class DeleteUserForm extends JetstreamDeleteUserForm
{
    /**
     * Delete the current user and flush their session.
     *
     * @param  Request  $request  Current HTTP request.
     * @param  DeletesUsers  $deleter  User deletion contract service.
     * @param  StatefulGuard  $auth  Stateful authentication guard.
     *
     * @throws ValidationException If password verification fails for password-authenticated users.
     */
    public function deleteUser(Request $request, DeletesUsers $deleter, StatefulGuard $auth): Redirector|RedirectResponse
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
