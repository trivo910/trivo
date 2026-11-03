<?php

namespace App\Jobs;

use App\Models\Media;
use App\Services\Media\ImageProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessMedia implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 60;

    public function __construct(public int $mediaId) {}

    public function handle(ImageProcessor $processor): void
    {
        $processor->process($this->mediaId);
    }

    public function failed(?\Throwable $exception): void
    {
        Media::query()->whereKey($this->mediaId)->whereIn('processing_status', ['queued', 'processing'])
            ->update(['processing_status' => 'failed', 'error_code' => 'worker_failed']);
    }
}
