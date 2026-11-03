<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->can('media.manage');
    }

    public function view(User $user, Media $media): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Media $media): bool
    {
        return $this->viewAny($user) && ! $media->trashed();
    }

    public function delete(User $user, Media $media): bool
    {
        return $this->update($user, $media) && ! $media->isLegacy() && in_array($media->processing_status, ['ready', 'queued', 'failed'], true);
    }

    public function restore(User $user, Media $media): bool
    {
        return $this->viewAny($user) && $media->trashed() && ! $media->isLegacy();
    }

    public function retry(User $user, Media $media): bool
    {
        return $this->update($user, $media) && ! $media->isLegacy() && $media->processing_status === 'failed';
    }
}
