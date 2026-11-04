<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->can('pages.view');
    }

    public function view(User $user, Page $page): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->can('pages.create');
    }

    public function updateSeo(User $user, Page $page): bool
    {
        return $user->is_active && $user->can('pages.seo') && ! $page->trashed();
    }

    public function update(User $user, Page $page): bool
    {
        return $user->is_active && $user->can('pages.update') && ! $page->trashed()
            && $page->status === 'draft' && $page->published_at === null;
    }

    public function delete(User $user, Page $page): bool
    {
        return $this->update($user, $page) && $user->can('pages.delete') && $page->legacy_wp_id === null && $page->publication_locked_at === null;
    }

    public function restore(User $user, Page $page): bool
    {
        return $user->is_active && $user->can('pages.delete') && $page->trashed()
            && $page->status === 'draft' && $page->published_at === null && $page->legacy_wp_id === null && $page->publication_locked_at === null;
    }

    public function publish(User $user, Page $page): bool
    {
        return $user->is_active && $user->can('pages.publish') && ! $page->trashed() && in_array($page->status, ['draft', 'scheduled'], true);
    }

    public function cancelPublication(User $user, Page $page): bool
    {
        return $this->publish($user, $page) && $page->status === 'scheduled';
    }
}
