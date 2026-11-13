<?php

namespace App\Actions\Admin;

use App\Models\Client;
use App\Models\Faq;
use App\Models\PortfolioItem;
use App\Models\Revision;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Gate;

class RestoreCatalogRevision
{
    public function handle(Service|PortfolioItem|Faq|Testimonial|Client $service, Revision $revision, int $version): void
    {
        Gate::authorize('update', $service);
        $snapshot = $revision->snapshot ?? [];
        abort_unless($revision->revisionable_type === $service::class && (int) $revision->revisionable_id === $service->id
            && ($snapshot['kind'] ?? null) === 'content', 404);

        app(SaveCatalogDraft::class)->handle($service, ['title' => $snapshot['title'] ?? null, 'path' => $service->path,
            'blocks' => $snapshot['blocks'] ?? [], 'media_id' => $snapshot['media_id'] ?? null, 'sort' => $snapshot['sort'] ?? 0,
            'content_version' => $version]);
    }
}
