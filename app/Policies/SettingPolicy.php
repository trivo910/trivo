<?php

namespace App\Policies;

use App\Models\User;

class SettingPolicy
{
    public function viewSite(User $user): bool
    {
        return $user->is_active && $user->can('settings.site');
    }

    public function updateSite(User $user): bool
    {
        return $this->viewSite($user);
    }

    public function viewAnalytics(User $user): bool
    {
        return $user->is_active && $user->can('settings.seo');
    }

    public function updateAnalytics(User $user): bool
    {
        return $this->viewAnalytics($user);
    }

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
