<?php

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

function adminAccount(string $role = 'Super Admin', bool $enrolled = true): User
{
    $user = User::factory()->create();
    $user->assignRole($role);
    if ($enrolled) {
        $user->forceFill(['two_factor_secret' => app(Google2FA::class)->generateSecretKey(32),
            'two_factor_confirmed_at' => now()])->save();
    }

    return $user->refresh();
}

/** @return array<string, int> */
function verifiedAdminSession(User $user): array
{
    return ['admin.verified_user_id' => $user->id, 'admin.auth_version' => $user->auth_version,
        'admin.last_activity' => now()->timestamp];
}
