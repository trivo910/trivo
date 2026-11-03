<?php

namespace App\Policies;

use App\Models\User;

class SettingPolicy
{
    public function viewMail(User $user): bool
    {
        return $user->is_active && $user->can('settings.mail');
    }

    public function updateMail(User $user): bool
    {
        return $this->viewMail($user);
    }

    public function testMail(User $user): bool
    {
        return $this->viewMail($user);
    }
}
