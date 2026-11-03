<?php

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class MediaAssets
{
    /** @return list<array{path: string, width: int, height: int, mime: string}> */
    public function variants(Media $media, ?string $mime = null): array
    {
        $values = $media->getAttribute('variants');
        $variants = [];
        foreach (is_array($values) ? $values : [] as $variant) {
            if (is_array($variant) && is_string($variant['path'] ?? null)
                && app(MediaPaths::class)->managedAsset($variant['path'])
                && in_array($variant['mime'] ?? '', ['image/avif', 'image/webp'], true)
                && ($mime === null || $variant['mime'] === $mime) && (int) ($variant['width'] ?? 0) > 0) {
                $variants[] = ['path' => $variant['path'], 'width' => (int) $variant['width'],
                    'height' => (int) $variant['height'], 'mime' => $variant['mime']];
            }
        }
        usort($variants, fn (array $a, array $b) => $a['width'] <=> $b['width']);

        return $variants;
    }

    public function srcset(Media $media, string $mime): string
    {
        return implode(', ', array_map(fn (array $variant): string => Storage::disk($media->disk)->url($variant['path']).' '.$variant['width'].'w', $this->variants($media, $mime)));
    }

    public function originalUrl(Media $media): string
    {
        if ($media->isLegacy()) {
            $path = ltrim($media->path, '/');
            if (! str_starts_with($path, 'wp-content/uploads/') || preg_match('~(?:^|/)\.{1,2}(?:/|$)|[\x00-\x1F\\\\?#]~', rawurldecode($path))) {
                throw new \DomainException('Invalid legacy media path.');
            }

            return asset(implode('/', array_map('rawurlencode', explode('/', $path))));
        }
        app(MediaPaths::class)->directory($media);

        return Storage::disk($media->disk)->url($media->path);
    }

    public function message(Media $media): string
    {
        return match ($media->processing_status) {
            'queued' => 'Queued for image processing.', 'processing' => 'Preparing responsive images.',
            'ready' => 'Image ready.', default => $media->error_code === 'unsupported_encoder'
                ? 'This server cannot create the required image formats. Contact an administrator.'
                : 'Image processing did not finish. Retry or contact an administrator.',
        };
    }
}
