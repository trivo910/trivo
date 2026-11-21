<?php

namespace App\Services\Content;

use App\Models\Media;
use App\Models\Post;
use App\Models\Revision;
use App\Support\ContentHtml;
use DOMDocument;
use DOMElement;
use Illuminate\Validation\ValidationException;

class BlogBody
{
    public function clean(string $body): string
    {
        if (! preg_match('/<\/?[a-z][^>]*>/i', $body)) {
            $paragraphs = preg_split('/\R\s*\R/', trim($body)) ?: [];
            $body = implode('', array_map(fn (string $paragraph) => '<p>'.nl2br(e($paragraph)).'</p>', $paragraphs));
        }

        $body = app(ContentHtml::class)->clean($body);

        return preg_replace('/<(\/?)h1(?=[\s>])/i', '<$1h2', $body) ?? $body;
    }

    /** @return list<string> */
    private function sources(string $body): array
    {
        if (trim($body) === '') {
            return [];
        }
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.$body);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $sources = [];
        foreach ($document->getElementsByTagName('img') as $image) {
            $sources[] = $image->getAttribute('src');
        }

        return array_values(array_unique(array_filter($sources)));
    }

    /** Called while saving in a transaction, to serialize saves with media archival. */
    public function lockImages(Post $post, string $body, ?int $featuredId): void
    {
        $sources = $this->sources($body);
        $previous = $this->sources(app(ContentHtml::class)->clean($post->body));
        if ($post->exists && array_diff($sources, $previous) !== []) {
            foreach (Revision::query()->where('revisionable_type', Post::class)->where('revisionable_id', $post->id)
                ->where('snapshot->kind', 'post_draft')->orderBy('id')->lazy(50) as $revision) {
                $historicalBody = $revision->snapshot['content']['body'] ?? '';
                if (is_string($historicalBody)) {
                    $previous = array_merge($previous, $this->sources(app(ContentHtml::class)->clean($historicalBody)));
                }
            }
        }
        $paths = [];
        foreach ($sources as $source) {
            $path = rawurldecode(ltrim((string) parse_url($source, PHP_URL_PATH), '/'));
            $paths[$source] = str_starts_with($path, 'storage/') ? substr($path, 8) : $path;
        }
        $images = Media::withTrashed()->where(function ($query) use ($paths, $featuredId): void {
            $query->whereIn('path', array_values($paths));
            if ($featuredId) {
                $query->orWhere('id', $featuredId);
            }
        })->orderBy('id')->lockForUpdate()->get();
        if ($featuredId && ! $images->contains('id', $featuredId)) {
            throw ValidationException::withMessages(['featured_media_id' => 'Choose an active featured image from the media library.']);
        }
        foreach ($images as $image) {
            if ($image->trashed() || $image->processing_status !== 'ready' || blank($image->alt)
                || ! in_array($image->mime, ['image/jpeg', 'image/png', 'image/webp', 'image/avif'], true)) {
                throw ValidationException::withMessages(['body' => 'Use ready, active library images with alt text.']);
            }
        }
        foreach ($paths as $source => $path) {
            $image = $images->firstWhere('path', $path);
            if (! $image && ! in_array($source, $previous, true)) {
                throw ValidationException::withMessages(['body' => 'Choose new content images from the media library. Existing source images may be retained.']);
            }
            if ($image && ! in_array($source, $previous, true)) {
                $host = parse_url($source, PHP_URL_HOST);
                $allowedHosts = [parse_url((string) config('app.url'), PHP_URL_HOST), request()->getHost(), 'trivo.in', 'www.trivo.in'];
                if ($host !== null && $host !== false && ! in_array($host, $allowedHosts, true)) {
                    throw ValidationException::withMessages(['body' => 'Use the library image URL rather than an external copy.']);
                }
            }
        }
    }

    /** @return array{html: string, toc: list<array{id: string, title: string}>} */
    public function tableOfContents(string $html, bool $enabled): array
    {
        if (! $enabled || trim($html) === '') {
            return ['html' => $html, 'toc' => []];
        }
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8"><html><body><div id="blog-toc-root">'.$html.'</div></body></html>');
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $root = (new \DOMXPath($document))->query('//*[@id="blog-toc-root"]')->item(0);
        if (! $root instanceof DOMElement) {
            return ['html' => $html, 'toc' => []];
        }
        $toc = [];
        foreach ((new \DOMXPath($document))->query('.//h2|.//h3', $root) as $heading) {
            if (! $heading instanceof DOMElement || trim($heading->textContent) === '') {
                continue;
            }
            $id = 'article-section-'.(count($toc) + 1);
            $heading->setAttribute('id', $id);
            $toc[] = ['id' => $id, 'title' => trim($heading->textContent)];
        }
        $html = '';
        foreach ($root->childNodes as $child) {
            $html .= $document->saveHTML($child);
        }

        return ['html' => $html, 'toc' => $toc];
    }
}
