<?php

namespace App\Services\Content;

use App\Models\Page;
use App\Models\PortfolioItem;
use App\Models\Service;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublishDueContent
{
    /** @return array{published: int, blocked: int} */
    public function handle(string $kind, int $limit = 50): array
    {
        $model = match ($kind) {
            'pages' => Page::class, 'services' => Service::class, 'portfolio' => PortfolioItem::class, default => throw new \InvalidArgumentException('Unsupported publication kind.')
        };
        $prefix = match ($kind) {
            'portfolio' => 'portfolio_', 'services' => 'service_', default => 'page_'
        };
        $counts = ['published' => 0, 'blocked' => 0];
        $lock = Cache::lock('content-path-writes', 120);
        if (! $lock->get()) {
            return $counts;
        }
        try {
            $ids = $model::query()->where('status', 'scheduled')->whereNotNull('published_at')->where('published_at', '<=', now())
                ->orderByRaw('CASE WHEN publication_error IS NULL THEN 0 ELSE 1 END')
                ->orderByRaw('CASE WHEN publication_error IS NOT NULL THEN updated_at ELSE published_at END')
                ->orderBy('id')->limit(max(1, min(100, $limit)))->pluck('id');
            $deadline = microtime(true) + 45;
            foreach ($ids as $id) {
                if (microtime(true) >= $deadline) {
                    break;
                }
                $result = DB::transaction(function () use ($id, $model, $prefix): ?string {
                    $page = $model::query()->lockForUpdate()->find($id);
                    if (! $page || $page->status !== 'scheduled' || ! $page->published_at || $page->published_at->isFuture()) {
                        return null;
                    }
                    try {
                        app(ContentPublication::class)->check($page);
                    } catch (ValidationException) {
                        if ($page->publication_error !== 'validation_failed') {
                            $page->publication_error = 'validation_failed';
                            $page->content_version++;
                            $page->save();
                            app(ContentPublication::class)->record($page, $prefix.'publication_blocked', null);
                        } else {
                            // Rotate failed batches so an older blocked page cannot starve repaired pages.
                            $page->touch();
                        }

                        return 'blocked';
                    }
                    $page->status = 'published';
                    $page->publication_error = null;
                    $page->publication_locked_at ??= now();
                    $page->content_version++;
                    $page->save();
                    app(ContentPublication::class)->record($page, $prefix.'scheduled_published', null);

                    return 'published';
                });
                if ($result !== null) {
                    $counts[$result]++;
                }
            }
        } finally {
            $lock->release();
        }

        return $counts;
    }
}
