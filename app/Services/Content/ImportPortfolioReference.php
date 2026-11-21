<?php

namespace App\Services\Content;

use App\Models\Media;
use App\Models\Page;
use App\Models\PortfolioItem;
use App\Models\Post;
use App\Models\Revision;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ImportPortfolioReference
{
    /** @param array<string, mixed> $data */
    public function handle(array $data): PortfolioItem
    {
        return Cache::lock('content-path-writes', 60)->block(5, fn () => DB::transaction(function () use ($data): PortfolioItem {
            $existing = PortfolioItem::withTrashed()->where('path', $data['path'])->first();
            if ($existing) {
                return $existing;
            }
            if (Page::withTrashed()->where('path', $data['path'])->exists()) {
                throw new \RuntimeException('A Page already owns this URL. Resolve its ownership before importing.');
            }
            $source = Post::withTrashed()->where('path', $data['path'])->lockForUpdate()->first();
            if ($source && (! in_array($source->title, $data['source_titles'] ?? [$data['title']], true) || $source->trashed())) {
                throw new \RuntimeException('The existing URL belongs to another source record. Import stopped.');
            }
            $layout = $data['layout'];
            $layout['thumbnail_media_id'] = $this->image($layout['thumbnail_path'], $data['title'].' portfolio thumbnail');
            unset($layout['thumbnail_path']);
            foreach ($layout['sections'] as &$section) {
                $section['image_id'] = empty($section['image_path']) ? null : $this->image($section['image_path'], $section['title']);
                unset($section['image_path']);
                foreach ($section['cards'] as &$card) {
                    $card['image_id'] = empty($card['image_path']) ? null : $this->image($card['image_path'], $card['title'].' portfolio project');
                    unset($card['image_path']);
                }
                unset($card);
            }
            unset($section);
            $layout = app(PortfolioLayout::class)->validate($layout);
            $sourceSnapshot = $source?->getAttributes();
            if ($source) {
                $retainedPath = '/portfolio-source/'.trim($data['path'], '/').'-'.$source->id;
                if (! app(ContentPaths::class)->available($retainedPath, $source->id, 'posts')) {
                    throw new \RuntimeException('The retained source path is already in use.');
                }
                // Keep the original source body and ID, but transfer this single public URL to Portfolio.
                Revision::query()->create(['revisionable_type' => Post::class, 'revisionable_id' => $source->id,
                    'snapshot' => ['kind' => 'portfolio_source_transfer', 'source' => $sourceSnapshot]]);
                $source->forceFill(['path' => $retainedPath, 'status' => 'draft', 'content_kind' => 'portfolio_source', 'sitemap_include' => false,
                    'content_version' => $source->content_version + 1])->save();
            }
            $blocks = app(PortfolioLayout::class)->blocks($layout);
            app(ContentDrafts::class)->validate(['title' => $data['title'], 'path' => $data['path'], 'blocks' => $blocks], new PortfolioItem);
            app(ContentDrafts::class)->lockImages($blocks);
            $portfolio = new PortfolioItem([
                ...$data['seo'], 'title' => $data['title'], 'path' => $data['path'], 'sort' => $data['sort'] ?? 0,
                'media_id' => $layout['thumbnail_media_id'], 'blocks' => $blocks, 'status' => 'published',
                'details' => ['case_study' => $layout, 'source' => ['reference_url' => $data['reference_url'],
                    'post_id' => $source?->id, 'original_path' => $data['path']]], 'content_version' => 1,
            ]);
            $portfolio->forceFill(['published_at' => $source->published_at ?? now(), 'publication_locked_at' => now()])->save();
            Revision::query()->create(['revisionable_type' => PortfolioItem::class, 'revisionable_id' => $portfolio->id,
                'snapshot' => ['kind' => 'content', 'title' => $portfolio->title, 'path' => $portfolio->path,
                    'blocks' => $blocks, 'media_id' => $portfolio->media_id, 'sort' => $portfolio->sort,
                    'case_study' => $layout, 'content_version' => 1]]);
            return $portfolio;
        }));
    }

    private function image(string $path, string $alt): int
    {
        if (! str_starts_with($path, 'wp-content/uploads/') || str_contains($path, '..')) {
            throw new \RuntimeException('Invalid source image path.');
        }
        if (! is_file(public_path($path))) {
            $path = rawurldecode($path);
        }
        if (str_contains($path, '..') || str_contains($path, chr(0))) {
            throw new \RuntimeException('Invalid decoded source image path.');
        }
        $file = public_path($path);
        $size = is_file($file) ? getimagesize($file) : false;
        if ($size === false) {
            throw new \RuntimeException('Missing or unreadable source image: '.$path);
        }
        $image = Media::withTrashed()->where('path', $path)->first();
        if ($image && ($image->trashed() || $image->processing_status !== 'ready' || blank($image->alt))) {
            throw new \RuntimeException('Source image needs review in the media library: '.$path);
        }
        $image ??= Media::query()->create(['disk' => 'public', 'path' => $path, 'original_name' => basename($path),
            'title' => $alt, 'alt' => $alt, 'mime' => $size['mime'], 'width' => $size[0], 'height' => $size[1],
            'size' => filesize($file), 'sha256' => hash_file('sha256', $file), 'processing_status' => 'ready']);

        return $image->id;
    }
}
