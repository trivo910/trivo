<?php

namespace App\Actions\Admin;

use App\Models\Media;
use Illuminate\Support\Facades\Gate;

class UpdateMedia
{
    /** @param array<string, mixed> $values */
    public function handle(Media $media, array $values): void
    {
        Gate::authorize('update', $media);
        $media->fill(array_intersect_key($values, array_flip(['alt', 'title', 'caption', 'folder'])))->save();
        activity('media')->causedBy(auth()->user())->performedOn($media)->event('image_metadata_updated')->log('image_metadata_updated');
    }
}
