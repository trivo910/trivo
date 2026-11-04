<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->can('services.view');
    }

    public function view(User $user, Service $service): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->can('services.create');
    }

    public function updateSeo(User $user, Service $service): bool
    {
        return $user->is_active && $user->can('services.seo') && ! $service->trashed();
    }

    public function update(User $user, Service $service): bool
    {
        return $user->is_active && $user->can('services.update') && ! $service->trashed() && $service->status === 'draft' && $service->published_at === null;
    }

    public function delete(User $user, Service $service): bool
    {
        return $this->update($user, $service) && $user->can('services.delete') && $service->legacy_wp_id === null && $service->publication_locked_at === null;
    }

    public function restore(User $user, Service $service): bool
    {
        return $user->is_active && $user->can('services.delete') && $service->trashed() && $service->status === 'draft' && $service->legacy_wp_id === null
            && $service->published_at === null && $service->publication_locked_at === null;
    }

    public function publish(User $user, Service $service): bool
    {
        return $user->is_active && $user->can('services.publish') && ! $service->trashed() && in_array($service->status, ['draft', 'scheduled'], true);
    }

    public function cancelPublication(User $user, Service $service): bool
    {
        return $this->publish($user, $service) && $service->status === 'scheduled';
    }
}
