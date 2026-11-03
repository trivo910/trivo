<?php

namespace App\Services\Media;

use Illuminate\Validation\ValidationException;

class RasterInspector
{
    public const TYPES = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
        'webp' => 'image/webp', 'avif' => 'image/avif'];

    /** @return array{mime: string, width: int, height: int, size: int, sha256: string} */
    public function inspect(string $path, string $extension, string $name = ''): array
    {
        $extension = strtolower($extension);
        $size = is_file($path) ? filesize($path) : false;
        if ($size === false || $size < 1 || $size > config('media.max_bytes')) {
            $this->reject('Choose an image no larger than 8 MiB.');
        }
        if (! isset(self::TYPES[$extension]) || preg_match('/(?:^|\.)(?:php\d*|phtml|phar|cgi|asp|aspx|shtml)(?:\.|$)/i', $name)) {
            $this->reject('Only JPEG, PNG, WebP and AVIF image files are accepted.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        $info = @getimagesize($path);
        if ($mime !== self::TYPES[$extension] || $info === false || $info['mime'] !== $mime) {
            $this->reject('The file content does not match a supported image type.');
        }
        [$width, $height] = $info;
        if ($width < 1 || $height < 1 || $width > config('media.max_dimension') || $height > config('media.max_dimension')
            || $width * $height > config('media.max_pixels')) {
            $this->reject('Images must be at most 6,000 pixels per side and 8 megapixels in total.');
        }
        $bytes = file_get_contents($path);
        if ($bytes === false || preg_match('/<\?(?:php\b|=)|<script\b|<svg\b|<!doctype\b/i', $bytes)
            || ! $this->validContainer($bytes, $mime)) {
            $this->reject('This image contains unsupported or unsafe embedded data.');
        }

        return ['mime' => $mime, 'width' => $width, 'height' => $height, 'size' => $size, 'sha256' => hash('sha256', $bytes)];
    }

    private function validContainer(string $bytes, string $mime): bool
    {
        if ($mime === 'image/jpeg') {
            return str_ends_with(rtrim($bytes, "\r\n\t "), "\xff\xd9");
        }
        if ($mime === 'image/png') {
            return str_ends_with($bytes, "\x00\x00\x00\x00IEND\xae\x42\x60\x82");
        }
        if ($mime === 'image/webp') {
            return strlen($bytes) >= 12 && (unpack('Vsize', substr($bytes, 4, 4))['size'] ?? -1) + 8 === strlen($bytes);
        }
        $offset = 0;
        $length = strlen($bytes);
        while ($offset < $length) {
            if ($offset + 8 > $length) {
                return false;
            }
            $size = unpack('Nsize', substr($bytes, $offset, 4))['size'];
            if ($size === 0) {
                return true;
            }
            if ($size === 1) {
                if ($offset + 16 > $length) {
                    return false;
                }
                $parts = unpack('Nhigh/Nlow', substr($bytes, $offset + 8, 8));
                if ($parts['high'] !== 0 || $parts['low'] < 16) {
                    return false;
                }
                $size = $parts['low'];
            }
            if ($size < 8 || $size > $length - $offset) {
                return false;
            }
            $offset += $size;
        }

        return $offset === $length;
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['image' => $message]);
    }
}
