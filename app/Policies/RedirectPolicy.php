<?php

namespace App\Policies;

use App\Models\Redirect;
use App\Models\User;

class RedirectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->can('redirects.manage');
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Redirect $redirect): bool
    {
        return $this->viewAny($user) && ! $redirect->is_regex;
    }

    public function delete(User $user, Redirect $redirect): bool
    {
        return $this->update($user, $redirect);
    }
}
