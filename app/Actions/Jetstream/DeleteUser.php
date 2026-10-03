<?php

namespace App\Actions\Jetstream;

use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user and all associated personal and location data.
     */
    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Delete all associated locations and their child forecast/transit data
            $user->locations()->each(function (Location $location) {
                $location->weatherConditions()->delete();
                $location->issTransits()->delete();
                $location->delete();
            });

            // Invalidate and remove active sessions and password reset tokens
            DB::table('sessions')->where('user_id', $user->id)->delete();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            // Revoke tokens and delete profile photo
            $user->tokens->each->delete();
            $user->deleteProfilePhoto();

            // Permanently delete user record
            $user->delete();

            Cache::forget('admin_dashboard_stats_v2');
        });
    }
}
