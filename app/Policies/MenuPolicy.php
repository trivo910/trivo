<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;

class MenuPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->can('menus.view');
    }

    public function view(User $user, Menu $menu): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->can('menus.create');
    }

    public function update(User $user, Menu $menu): bool
    {
        return $user->is_active && $user->can('menus.update') && ! $menu->trashed();
    }

    public function delete(User $user, Menu $menu): bool
    {
        return $this->update($user, $menu) && $user->can('menus.delete') && $menu->legacy_wp_id === null;
    }

    public function restore(User $user, Menu $menu): bool
    {
        return $user->is_active && $user->can('menus.delete') && $menu->trashed() && $menu->legacy_wp_id === null;
    }

    public function archiveItem(User $user, Menu $menu): bool
    {
        return $this->update($user, $menu) && $user->can('menus.delete');
    }
}
