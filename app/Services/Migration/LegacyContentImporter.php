<?php

namespace App\Services\Migration;

use App\Models\Page;
use App\Models\Post;
use App\Support\ContentHtml;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LegacyContentImporter
{
    /**
     * @return array{pages: int, posts: int, updated: int, skipped_existing: int, skipped_archived: int, url_conflicts: int, missing_map: int}
     */
    public function import(bool $dryRun = false): array
    {
        return $dryRun ? $this->perform(true) : DB::transaction(fn (): array => $this->perform(false));
    }

    /**
     * @return array{pages: int, posts: int, updated: int, skipped_existing: int, skipped_archived: int, url_conflicts: int, missing_map: int}
     */
    private function perform(bool $dryRun): array
    {
        $urlMap = $this->urlMap();
        $postRows = DB::connection('legacy')->table('wp_posts')
            ->whereIn('post_type', ['page', 'post'])
            ->orderBy('ID')
            ->get();
        $metadata = DB::connection('legacy')->table('wp_postmeta')
            ->whereIn('post_id', $postRows->pluck('ID')->all())
            ->whereIn('meta_key', ['rank_math_title', 'rank_math_description', 'rank_math_focus_keyword'])
            ->get()
            ->groupBy('post_id');
        $pagePaths = [];
        $postPathIds = [];
        foreach ($urlMap as $rows) {
            foreach ($rows as $row) {
                if ($row['type'] === 'page') {
                    $pagePaths[$row['path']] = true;
                }
            }
        }
        foreach ($urlMap as $id => $rows) {
            if (isset($rows['post'])) {
                $postPathIds[$rows['post']['path']] = $id;
            }
        }

        $result = ['pages' => 0, 'posts' => 0, 'updated' => 0, 'skipped_existing' => 0,
            'skipped_archived' => 0, 'url_conflicts' => 0, 'missing_map' => 0];

        foreach ($postRows as $source) {
            $type = (string) $source->post_type;
            $legacyId = (int) $source->ID;
            $map = $urlMap[$legacyId][$type] ?? null;
            if (! $map) {
                $result['missing_map']++;

                continue;
            }

            $path = $map['path'];
            if ($type === 'page' && $path === '/marketing-solutions') {
                $result['skipped_existing']++;

                continue;
            }

            $modelClass = $type === 'page' ? Page::class : Post::class;
            $existing = $type === 'page'
                ? Page::withTrashed()->where('legacy_wp_id', $legacyId)->first()
                : Post::withTrashed()->where('legacy_wp_id', $legacyId)->first();
            if ($existing?->trashed()) {
                $result['skipped_archived']++;

                continue;
            }

            $allowedSourcePostId = $type === 'page' ? ($postPathIds[$path] ?? null) : null;
            if (! $existing && $this->pathIsLocallyOccupied($path, $type === 'page' ? 'pages' : 'posts', $allowedSourcePostId)) {
                $result['skipped_existing']++;

                continue;
            }

            $meta = [];
            foreach ($metadata->get($legacyId, collect()) as $item) {
                $meta[(string) $item->meta_key] = (string) $item->meta_value;
            }
            $body = app(ContentHtml::class)->clean((string) $source->post_content);
            $robots = trim((string) $map['robots']);
            $noindex = (bool) preg_match('/(?:^|[\s,])noindex(?:$|[\s,])/i', $robots);
            $nofollow = (bool) preg_match('/(?:^|[\s,])nofollow(?:$|[\s,])/i', $robots);
            $publishedAt = $source->post_date ?: null;
            $modifiedAt = $source->post_modified ?: $publishedAt;
            $pathConflict = $type === 'post' && isset($pagePaths[$path]);

            $attributes = [
                'legacy_wp_id' => $legacyId,
                'title' => (string) $source->post_title,
                'path' => $path,
                'status' => $type === 'post' && $pathConflict ? 'draft' : 'published',
                'published_at' => $publishedAt,
                'seo_title' => $meta['rank_math_title'] ?? $map['title'],
                'seo_description' => $meta['rank_math_description'] ?? $map['meta_description'],
                'canonical_url' => $map['canonical'] ?: null,
                'robots' => $robots ?: null,
                'noindex' => $noindex,
                'nofollow' => $nofollow,
                'sitemap_include' => ! $noindex && $map['status'] === '200',
            ];

            if ($type === 'page') {
                $attributes += [
                    'template' => 'page',
                    'blocks' => [['type' => 'legacy_html', 'html' => $body]],
                    'content_version' => 1,
                ];
            } else {
                $attributes += [
                    'slug' => (string) $source->post_name,
                    'excerpt' => null,
                    'body' => $body,
                    'reading_time' => max(1, (int) ceil(str_word_count(strip_tags($body)) / 200)),
                    'focus_keyword' => $meta['rank_math_focus_keyword'] ?? null,
                    'content_version' => 1,
                ];
            }

            if (! $dryRun) {
                $model = $existing ?? new $modelClass;
                $model->forceFill($attributes);
                $model->forceFill(['created_at' => $publishedAt, 'updated_at' => $modifiedAt]);
                $model->save();
            }

            $result[$type === 'page' ? 'pages' : 'posts']++;
            if ($existing) {
                $result['updated']++;
            }
            if ($pathConflict) {
                $result['url_conflicts']++;
            }
        }

        return $result;
    }

    /**
     * @return array<int, array<string, array{path: string, type: string, title: string, meta_description: string, canonical: string, robots: string, status: string}>>
     */
    private function urlMap(): array
    {
        $file = base_path('docs/URL-MAP.csv');
        if (! is_file($file) || ! ($handle = fopen($file, 'rb'))) {
            throw new \RuntimeException('docs/URL-MAP.csv is required to preserve verified source paths.');
        }

        $headers = fgetcsv($handle);
        if (! is_array($headers)) {
            fclose($handle);
            throw new \RuntimeException('docs/URL-MAP.csv has no header row.');
        }
        $headers[0] = ltrim((string) $headers[0], "\xEF\xBB\xBF");

        $rows = [];
        $paths = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) !== count($headers)) {
                continue;
            }
            $row = array_combine($headers, $values);
            $id = (int) ($row['legacy_wp_id'] ?? 0);
            $type = (string) ($row['type'] ?? '');
            if ($id < 1 || ! in_array($type, ['page', 'post'], true)) {
                continue;
            }
            $path = (string) ($row['path'] ?? '');
            if ($path === '') {
                $canonical = (string) ($row['canonical'] ?? '');
                $path = parse_url($canonical, PHP_URL_PATH) ?: '/';
            }
            if (! str_starts_with($path, '/')) {
                continue;
            }
            $mapped = [
                'path' => $path,
                'type' => $type,
                'title' => (string) ($row['title'] ?? ''),
                'meta_description' => (string) ($row['meta_description'] ?? ''),
                'canonical' => (string) ($row['canonical'] ?? ''),
                'robots' => (string) ($row['robots'] ?? ''),
                'status' => (string) ($row['final_status'] ?? $row['status'] ?? ''),
            ];
            $rows[$id][$type] = $mapped;
            $paths[$path] = $mapped;
        }
        fclose($handle);

        $conflictFile = base_path('docs/URL-CONFLICTS.csv');
        if (is_file($conflictFile) && ($conflictHandle = fopen($conflictFile, 'rb'))) {
            $conflictHeaders = fgetcsv($conflictHandle);
            while (($values = fgetcsv($conflictHandle)) !== false) {
                if (! is_array($conflictHeaders) || count($values) !== count($conflictHeaders)) {
                    continue;
                }
                $conflict = array_combine($conflictHeaders, $values);
                $id = (int) ($conflict['legacy_wp_id'] ?? 0);
                $type = (string) ($conflict['type'] ?? '');
                $path = (string) ($conflict['path'] ?? '');
                if ($id < 1 || ! in_array($type, ['page', 'post'], true) || $path === '' || isset($rows[$id][$type])) {
                    continue;
                }

                $mapped = $paths[$path] ?? [
                    'path' => $path,
                    'type' => $type,
                    'title' => '',
                    'meta_description' => '',
                    'canonical' => 'https://trivo.in'.$path,
                    'robots' => 'noindex, nofollow',
                    'status' => '200',
                ];
                $mapped['type'] = $type;
                $rows[$id][$type] = $mapped;
            }
            fclose($conflictHandle);
        }

        return $rows;
    }

    private function pathIsLocallyOccupied(string $path, string $ownerTable, ?int $allowedSourcePostId = null): bool
    {
        foreach (['pages', 'posts', 'categories', 'tags', 'authors', 'services', 'portfolio_items', 'faqs', 'testimonials', 'clients'] as $table) {
            if ($table === 'posts' && $ownerTable === 'pages' && $allowedSourcePostId !== null) {
                if (DB::table($table)->where('path', $path)->where(function ($query) use ($allowedSourcePostId): void {
                    $query->whereNull('legacy_wp_id')->orWhere('legacy_wp_id', '!=', $allowedSourcePostId);
                })->exists()) {
                    return true;
                }

                continue;
            }
            if ($table === $ownerTable) {
                if (DB::table($table)->where('path', $path)->exists()) {
                    return true;
                }

                continue;
            }
            if (DB::table($table)->where('path', $path)->exists()) {
                return true;
            }
        }

        return false;
    }
}
