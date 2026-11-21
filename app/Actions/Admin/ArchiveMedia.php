<?php

namespace App\Actions\Admin;

use App\Models\Media;
use App\Services\Media\MediaUsage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ArchiveMedia
{
    public function handle(Media $media): void
    {
        Gate::authorize('delete', $media);
        DB::transaction(function () use ($media) {
            $media = Media::query()->lockForUpdate()->findOrFail($media->id);
            Gate::authorize('delete', $media);
            $references = app(MediaUsage::class)->references($media);
            if (collect($references)->contains('type', 'unreadable_settings')) {
                throw ValidationException::withMessages(['media' => 'Image usage cannot be fully checked. Restore or re-save the unreadable settings before archiving.']);
            }
            if ($references !== []) {
                throw ValidationException::withMessages(['media' => 'This image is in use. Remove its references before archiving.']);
            }
            $media->delete();
            // Keep published files: archiving must not break indexed/external image URLs.
            activity('media')->causedBy(auth()->user())->performedOn($media)->event('image_archived')->log('image_archived');
        });
    }
}
