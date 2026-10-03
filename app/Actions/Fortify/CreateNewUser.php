<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        // 1. Anti-bot honeypot check
        if (!empty($input['website_url'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'name' => ['Registration request could not be processed.'],
            ]);
        }

        // 2. Minimum form submission timing check (ignore in testing unless explicitly provided)
        if (isset($input['form_time']) || !app()->environment('testing')) {
            try {
                $formTime = decrypt($input['form_time'] ?? '');
                if (!is_numeric($formTime) || (microtime(true) - (float) $formTime) < 1.5) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'name' => ['Please take a moment before submitting the registration form.'],
                    ]);
                }
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'name' => ['Invalid registration session. Please reload the page.'],
                ]);
            }
        }

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
