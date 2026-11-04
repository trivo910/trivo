<?php

namespace App\Services\Content;

use App\Support\RedirectPaths;
use Illuminate\Support\Facades\DB;

class ContentPaths
{
    public function available(string $path, ?int $ownerId = null, string $ownerTable = 'pages'): bool
    {
        if (! in_array($ownerTable, ['pages', 'services', 'portfolio_items'], true)) {
            throw new \InvalidArgumentException('Unsupported content owner.');
        }
        if ($ownerTable !== 'pages' && $path === '/') {
            return false;
        }
        if ($path !== '/' && ! RedirectPaths::source($path)) {
            return false;
        }
        $decoded = $path;
        for ($i = 0; $i < 4; $i++) {
            $decoded = rawurldecode($decoded);
        }
        if (preg_match('/[%<>"\x00-\x20\x7F?#\\\\]/', $decoded) || str_contains($decoded, '//')) {
            return false;
        }
        $first = strtolower(explode('/', ltrim($decoded, '/'))[0]);
        if (in_array($first, ['build', 'storage', 'fonts', 'wp-content', 'uploads', 'api', 'up', 'robots.txt', 'feed',
            'sitemap.xml', 'sitemap_index.xml', 'wp-sitemap.xml', 'wp-json', 'wp-admin', 'wp-login.php', 'xmlrpc.php'], true)
            || str_starts_with($first, '.') || str_contains(strtolower($decoded), '.php') || str_contains(strtolower($first), 'sitemap')) {
            return false;
        }
        // Trailing-slash aliases are reserved together; preserve the submitted canonical path.
        $key = strtolower(rtrim($decoded, '/'));
        $encoded = strtolower(rtrim($path, '/'));
        foreach (['pages', 'posts', 'categories', 'tags', 'authors', 'services', 'portfolio_items'] as $table) {
            $query = DB::table($table)->whereRaw('LOWER(path) IN (?, ?, ?, ?)', [$key, $key.'/', $encoded, $encoded.'/']);
            if ($table === $ownerTable && $ownerId !== null) {
                $query->where('id', '!=', $ownerId);
            }
            if ($query->exists()) {
                return false;
            }
        }

        return ! DB::table('redirects')->whereRaw('LOWER(from_path) IN (?, ?, ?, ?)', [$key, $key.'/', $encoded, $encoded.'/'])->exists();
    }
}
