<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Services\Content\BlogTerms;

class BlogTermPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->can('posts.view');
    }

    public function view(User $user, Category|Tag $term): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->can('posts.create');
    }

    public function update(User $user, Category|Tag $term): bool
    {
        return $user->is_active && $user->can('posts.update') && ! $term->trashed();
    }

    public function delete(User $user, Category|Tag $term): bool
    {
        return $this->update($user, $term) && $user->can('posts.delete') && $term->legacy_wp_id === null && ! app(BlogTerms::class)->referenced($term);
    }

    public function restore(User $user, Category|Tag $term): bool
    {
        return $user->is_active && $user->can('posts.delete') && $term->trashed() && $term->legacy_wp_id === null;
    }
}
