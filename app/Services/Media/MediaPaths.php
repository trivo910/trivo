<?php

namespace App\Services\Media;

use App\Models\Media;

class MediaPaths
{
    public function directory(Media $media): string
    {
        if ($media->isLegacy() || ! preg_match('~\A(media/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/original\.(?:jpg|png)\z~D', $media->path, $match)) {
            throw new \DomainException('This media entry is not a managed upload.');
        }

        return $match[1];
    }

    public function staging(Media $media): string
    {
        return 'media-staging/'.basename($this->directory($media));
    }

    public function managedAsset(string $path): bool
    {
        return (bool) preg_match('~\Amedia/[a-f0-9-]{36}/(?:original\.(?:jpg|png)|[0-9]{1,4}\.(?:webp|avif))\z~D', $path);
    }
}
