<?php

namespace App\Policies;

use App\Models\User;

class NotFoundLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->can('redirects.manage');
    }
}
