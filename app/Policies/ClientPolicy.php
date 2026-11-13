<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->can('clients.view');
    }

    public function view(User $user, Client $client): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->can('clients.create');
    }

    public function update(User $user, Client $client): bool
    {
        return $user->is_active && $user->can('clients.update') && ! $client->trashed() && $client->status === 'draft' && $client->published_at === null;
    }

    public function delete(User $user, Client $client): bool
    {
        return $this->update($user, $client) && $user->can('clients.delete') && $client->legacy_wp_id === null && $client->publication_locked_at === null;
    }

    public function restore(User $user, Client $client): bool
    {
        return $user->is_active && $user->can('clients.delete') && $client->trashed() && $client->status === 'draft'
            && $client->published_at === null && $client->legacy_wp_id === null && $client->publication_locked_at === null;
    }
}
