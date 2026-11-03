<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function updatePreferences(User $actor, User $user): bool
    {
        return $actor->is_active && $actor->id === $user->id;
    }

    public function viewAny(User $actor): bool
    {
        return $actor->is_active && $actor->can('users.manage');
    }
}
