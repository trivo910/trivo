<?php

namespace App\Policies;

use App\Models\Author;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->can('posts.view');
    }

    public function view(User $user, Author $author): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->can('posts.create');
    }

    public function update(User $user, Author $author): bool
    {
        return $user->is_active && $user->can('posts.update') && ! $author->trashed();
    }

    public function updateSeo(User $user, Author $author): bool
    {
        return $user->is_active && $user->can('posts.seo') && ! $author->trashed();
    }

    public function delete(User $user, Author $author): bool
    {
        return $this->update($user, $author) && $user->can('posts.delete') && $author->legacy_wp_id === null
            && ! DB::table('posts')->where('author_id', $author->id)->exists();
    }

    public function restore(User $user, Author $author): bool
    {
        return $user->is_active && $user->can('posts.delete') && $author->trashed() && $author->legacy_wp_id === null;
    }
}
