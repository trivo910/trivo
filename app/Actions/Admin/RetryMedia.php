<?php

namespace App\Actions\Admin;

use App\Jobs\ProcessMedia;
use App\Models\Media;
use Illuminate\Support\Facades\Gate;

class RetryMedia
{
    public function handle(Media $media): void
    {
        Gate::authorize('retry', $media);
        if (Media::query()->whereKey($media->id)->where('processing_status', 'failed')
            ->update(['processing_status' => 'queued', 'error_code' => null]) === 1) {
            try {
                ProcessMedia::dispatch($media->id)->onConnection('database')->afterCommit();
            } catch (\Throwable) {
                $media->update(['processing_status' => 'failed', 'error_code' => 'queue_unavailable']);
            }
        }
    }
}
