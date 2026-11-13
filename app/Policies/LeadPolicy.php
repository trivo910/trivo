<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->can('leads.view');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Lead $lead): bool
    {
        return $this->viewAny($user) && $user->can('leads.manage');
    }

    public function export(User $user): bool
    {
        return $this->viewAny($user) && $user->can('leads.manage');
    }
}
