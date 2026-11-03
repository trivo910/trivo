<?php

namespace App\Services\Admin;

use App\Models\User;

class AuthAudit
{
    public function record(string $event, ?User $user, ?string $ip): void
    {
        activity('authentication')->causedBy($user)
            ->withProperties(['ip_hash' => hash_hmac('sha256', (string) $ip, (string) config('app.key'))])
            ->event($event)->log($event);
    }
}
