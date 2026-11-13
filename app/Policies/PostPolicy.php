<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->can('posts.view');
    }

    public function view(User $user, Post $post): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->can('posts.create');
    }

    public function updateSeo(User $user, Post $post): bool
    {
        return $user->is_active && $user->can('posts.seo') && ! $post->trashed();
    }

    public function update(User $user, Post $post): bool
    {
        return $user->is_active && $user->can('posts.update') && ! $post->trashed()
            && $post->status === 'draft' && $post->published_at === null;
    }

    public function delete(User $user, Post $post): bool
    {
        return $this->update($user, $post) && $user->can('posts.delete') && $post->legacy_wp_id === null && $post->publication_locked_at === null;
    }

    public function restore(User $user, Post $post): bool
    {
        return $user->is_active && $user->can('posts.delete') && $post->trashed() && $post->legacy_wp_id === null
            && $post->status === 'draft' && $post->published_at === null && $post->publication_locked_at === null;
    }

    public function publish(User $user, Post $post): bool
    {
        return $user->is_active && $user->can('posts.publish') && ! $post->trashed() && in_array($post->status, ['draft', 'scheduled'], true);
    }

    public function cancelPublication(User $user, Post $post): bool
    {
        return $this->publish($user, $post) && $post->status === 'scheduled';
    }
}
