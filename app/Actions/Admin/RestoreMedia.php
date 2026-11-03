<?php

namespace App\Actions\Admin;

use App\Jobs\ProcessMedia;
use App\Models\Media;
use Illuminate\Support\Facades\Gate;

class RestoreMedia
{
    public function handle(Media $media): void
    {
        Gate::authorize('restore', $media);
        $media->restore();
        if ($media->processing_status === 'queued') {
            ProcessMedia::dispatch($media->id)->onConnection('database')->afterCommit();
        }
        activity('media')->causedBy(auth()->user())->performedOn($media)->event('image_restored')->log('image_restored');
    }
}
