<?php

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageProcessor
{
    public function __construct(private MediaPaths $paths, private RasterInspector $inspector) {}

    public function process(int $id): void
    {
        $media = Media::query()->find($id);
        if (! $media || $media->isLegacy()) {
            return;
        }
        $directory = $this->paths->directory($media);
        if (Media::query()->whereKey($id)->where('processing_status', 'queued')->update(['processing_status' => 'processing']) !== 1) {
            return;
        }
        $staging = Storage::disk(config('media.staging_disk'));
        $public = Storage::disk($media->disk);
        $privateDirectory = $this->paths->staging($media);
        try {
            if (! extension_loaded('exif') || ! (gd_info()['AVIF Support'] ?? false) || ! (gd_info()['WebP Support'] ?? false)) {
                $media->update(['processing_status' => 'failed', 'error_code' => 'unsupported_encoder']);

                return;
            }
            $source = $staging->path($privateDirectory.'/upload.bin');
            $extension = array_search($media->source_mime, RasterInspector::TYPES, true);
            $inspection = $this->inspector->inspect($source, $extension === false ? '' : $extension);
            if (! hash_equals((string) $media->source_sha256, $inspection['sha256'])) {
                throw new \RuntimeException('Staging integrity mismatch.');
            }
            $manager = new ImageManager(Driver::class, autoOrientation: true, decodeAnimation: false, strip: true);
            $image = $manager->read($source);
            $original = (string) ($media->mime === 'image/jpeg' ? $image->toJpeg(quality: 85, strip: true) : $image->toPng());
            $outputs = [$media->path => $original];
            $variants = [];
            $widths = array_unique(array_map(fn (int $width): int => min($width, $image->width()), config('media.widths')));
            foreach ($widths as $width) {
                $resized = (clone $image)->scaleDown(width: $width);
                foreach (['webp', 'avif'] as $format) {
                    $encoded = (string) ($format === 'webp'
                        ? $resized->toWebp(quality: config('media.webp_quality'))
                        : $resized->toAvif(quality: config('media.avif_quality'), strip: true));
                    $path = $directory.'/'.$width.'.'.$format;
                    $outputs[$path] = $encoded;
                    $variants[] = ['path' => $path, 'width' => $resized->width(), 'height' => $resized->height(),
                        'mime' => 'image/'.$format, 'bytes' => strlen($encoded), 'sha256' => hash('sha256', $encoded)];
                }
            }
            // Every output is decoded/re-encoded before any bytes are published.
            foreach ($outputs as $path => $bytes) {
                if (! $public->put($path, $bytes)) {
                    throw new \RuntimeException('Image output storage unavailable.');
                }
            }
            $media->update(['processing_status' => 'ready', 'error_code' => null, 'variants' => $variants,
                'width' => $image->width(), 'height' => $image->height(), 'size' => strlen($original), 'sha256' => hash('sha256', $original)]);
        } catch (\Throwable) {
            try {
                $public->deleteDirectory($directory);
            } catch (\Throwable) {
                // Partial outputs are sanitized and remain hidden while the row is failed.
            }
            $media->update(['processing_status' => 'failed', 'error_code' => 'processing_failed']);

            return;
        }
        try {
            $staging->deleteDirectory($privateDirectory);
        } catch (\Throwable) {
            // A private cleanup failure must not remove an already published image.
        }
    }
}
