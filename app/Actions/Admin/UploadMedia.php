<?php

namespace App\Actions\Admin;

use App\Jobs\ProcessMedia;
use App\Models\Media;
use App\Services\Media\MediaPaths;
use App\Services\Media\RasterInspector;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UploadMedia
{
    /** @param array<string, mixed> $metadata */
    public function handle(UploadedFile $file, array $metadata): Media
    {
        Gate::authorize('create', Media::class);
        $info = app(RasterInspector::class)->inspect($file->getRealPath(), $file->getClientOriginalExtension(), $file->getClientOriginalName());
        try {
            return Cache::lock('media-upload:'.$info['sha256'], 30)->block(5, fn () => $this->store($file, $metadata, $info));
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages(['image' => 'This image is already being uploaded. Please try again shortly.']);
        }
    }

    /** @param array<string, mixed> $metadata
     * @param  array{mime: string, width: int, height: int, size: int, sha256: string}  $info
     */
    private function store(UploadedFile $file, array $metadata, array $info): Media
    {
        $existing = Media::withTrashed()->where('source_sha256', $info['sha256'])
            ->orWhere('sha256', $info['sha256'])->first();
        if ($existing) {
            if ($existing->trashed()) {
                app(RestoreMedia::class)->handle($existing);
            }
            if (! $existing->isLegacy() && $existing->processing_status === 'failed') {
                $source = app(MediaPaths::class)->staging($existing).'/upload.bin';
                if (Storage::disk(config('media.staging_disk'))->put($source, $file->getContent())) {
                    app(RetryMedia::class)->handle($existing);
                    $existing->refresh();
                }
            }

            return $existing;
        }
        $directory = 'media/'.Str::uuid();
        $extension = $info['mime'] === 'image/jpeg' ? 'jpg' : 'png';
        $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $name = mb_check_encoding($name, 'UTF-8') ? mb_substr(preg_replace('/[\x00-\x1F\x7F]/', '', $name) ?? 'upload', 0, 255) : 'upload';
        $media = DB::transaction(function () use ($directory, $extension, $metadata, $info, $name, $file): Media {
            $media = Media::query()->create(array_merge(array_intersect_key($metadata, array_flip(['alt', 'title', 'caption', 'folder'])), [
                'disk' => config('media.disk'), 'path' => $directory.'/original.'.$extension,
                'original_name' => $name, 'mime' => $extension === 'jpg' ? 'image/jpeg' : 'image/png',
                'size' => $info['size'], 'width' => $info['width'], 'height' => $info['height'],
                'source_mime' => $info['mime'], 'sha256' => $info['sha256'], 'source_sha256' => $info['sha256'],
                'processing_status' => 'queued', 'uploaded_by' => auth()->id(),
            ]));
            $source = app(MediaPaths::class)->staging($media).'/upload.bin';
            if (! Storage::disk(config('media.staging_disk'))->put($source, $file->getContent())) {
                throw ValidationException::withMessages(['image' => 'Could not store this upload. Please try again.']);
            }

            return $media;
        });
        try {
            ProcessMedia::dispatch($media->id)->onConnection('database')->afterCommit();
        } catch (\Throwable) {
            $media->update(['processing_status' => 'failed', 'error_code' => 'queue_unavailable']);
        }
        activity('media')->causedBy(auth()->user())->performedOn($media)->event('image_uploaded')->log('image_uploaded');

        return $media;
    }
}
