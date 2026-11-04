<?php

namespace App\Services\Content;

use App\Models\Media;
use App\Models\Page;
use App\Models\PortfolioItem;
use App\Models\Revision;
use App\Models\Service;
use Illuminate\Validation\ValidationException;

class ContentPublication
{
    /** Called inside a transaction while holding the page and content-path locks. */
    public function check(Page|Service|PortfolioItem $page): void
    {
        $drafts = app(ContentDrafts::class);
        $data = $drafts->validate(['title' => $page->title, 'path' => $page->path, 'blocks' => $page->blocks ?? [], 'content_version' => $page->content_version], $page);
        if ($data['blocks'] === [] || trim((string) $page->getAttribute('seo_title')) === '' || trim((string) $page->getAttribute('seo_description')) === '') {
            throw ValidationException::withMessages(['publication' => 'Add content sections, an SEO title and a meta description before publishing.']);
        }
        $seo = app(ContentSeo::class)->validate(array_merge(app(ContentSeo::class)->forForm($page), ['content_version' => $page->content_version]), $this->context($page));
        if ($page->getAttribute('noindex') !== $seo['noindex'] || $page->getAttribute('nofollow') !== $seo['nofollow']) {
            throw ValidationException::withMessages(['publication' => 'Save SEO to reconcile indexing flags before publishing.']);
        }
        $ids = array_merge(array_column($data['blocks'], 'media_id'), [$seo['og_image_id'], $seo['twitter_image_id']]);
        if (($page instanceof Service || $page instanceof PortfolioItem) && $page->media_id) {
            $ids[] = $page->media_id;
            $data['blocks'][] = ['type' => 'image', 'media_id' => $page->media_id];
        }
        // Lock the union first so content/social image groups cannot take locks in opposite orders.
        Media::query()->whereIn('id', array_filter($ids))->orderBy('id')->lockForUpdate()->get();
        $drafts->lockImages($data['blocks']);
        app(ContentSeo::class)->lockImages($seo);
    }

    public function record(Page|Service|PortfolioItem $page, string $event, ?int $userId): void
    {
        Revision::query()->create(['revisionable_type' => $page::class, 'revisionable_id' => $page->id, 'user_id' => $userId,
            'snapshot' => ['kind' => 'publication', 'event' => $event, 'status' => $page->status,
                'published_at' => $page->published_at?->toIso8601String(), 'content_version' => $page->content_version]]);
        $activity = activity('content')->performedOn($page)->event($event);
        if ($userId !== null) {
            $activity->causedBy($userId);
        } else {
            $activity->causedByAnonymous();
        }
        $activity->log($event);
    }

    public function context(Page|Service|PortfolioItem $model): string
    {
        return match (true) {
            $model instanceof PortfolioItem => 'portfolio', $model instanceof Service => 'service', default => 'page',
        };
    }
}
