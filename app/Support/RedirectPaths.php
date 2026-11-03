<?php

namespace App\Support;

class RedirectPaths
{
    public static function source(string $value): bool
    {
        return self::safePath($value) && $value !== '/' && ! str_contains($value, '?') && ! str_contains($value, '#')
            && ! in_array($value, ['/robots.txt', '/sitemap_index.xml'], true);
    }

    public static function target(string $value): ?string
    {
        if (str_starts_with($value, 'https://')) {
            $parts = parse_url($value);
            if (! is_array($parts) || isset($parts['user']) || isset($parts['pass'])
                || strtolower($parts['host'] ?? '') !== strtolower((string) parse_url(config('trivo.canonical_url'), PHP_URL_HOST))
                || isset($parts['port']) && $parts['port'] !== 443) {
                return null;
            }
            $value = ($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '')
                .(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
        }

        return self::safePath($value) ? $value : null;
    }

    private static function safePath(string $value): bool
    {
        if (! mb_check_encoding($value, 'UTF-8') || mb_strlen($value) > 768 || ! str_starts_with($value, '/') || str_starts_with($value, '//')
            || preg_match('/[\x00-\x20\x7F\\\\]/u', $value)) {
            return false;
        }
        $decoded = $value;
        for ($i = 0; $i < 4; $i++) {
            $decoded = rawurldecode($decoded);
        }
        if (str_starts_with($decoded, '//') || preg_match('/[\x00-\x1F\x7F\\\\]/u', $decoded)) {
            return false;
        }
        $path = strtolower((string) parse_url($decoded, PHP_URL_PATH));
        foreach (explode('/', $path) as $segment) {
            if (in_array($segment, ['.', '..'], true)) {
                return false;
            }
        }
        $admin = '/'.strtolower(trim((string) config('trivo.admin_path'), '/'));

        return ! str_starts_with($path, '/.') && ! in_array($path, ['/up', '/wp-login.php', '/xmlrpc.php'], true)
            && ! str_starts_with($path, '/wp-admin')
            && ($admin === '/' || $path !== $admin && ! str_starts_with($path, $admin.'/'));
    }
}
