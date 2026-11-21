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
        return $this->viewAny($user) && ($post->content_kind ?? 'article') === 'article';
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->can('posts.create');
    }

    public function updateSeo(User $user, Post $post): bool
    {
        return $user->is_active && $user->can('posts.seo') && ! $post->trashed() && ($post->content_kind ?? 'article') === 'article';
    }

    public function update(User $user, Post $post): bool
    {
        return $user->is_active && $user->can('posts.update') && ! $post->trashed()
            && ($post->content_kind ?? 'article') === 'article' && in_array($post->status, ['draft', 'published'], true);
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->is_active && $user->can('posts.delete') && ! $post->trashed()
            && ($post->content_kind ?? 'article') === 'article';
    }

    public function restore(User $user, Post $post): bool
    {
        return $user->is_active && $user->can('posts.delete') && $post->trashed()
            && ($post->content_kind ?? 'article') === 'article';
    }

    public function publish(User $user, Post $post): bool
    {
        return $user->is_active && $user->can('posts.publish') && ! $post->trashed()
            && ($post->content_kind ?? 'article') === 'article' && in_array($post->status, ['draft', 'scheduled'], true);
    }

    public function cancelPublication(User $user, Post $post): bool
    {
        return $this->publish($user, $post) && $post->status === 'scheduled';
    }
}
