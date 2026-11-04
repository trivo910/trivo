<?php

namespace App\Actions\Admin;

use App\Models\PortfolioItem;
use App\Models\Revision;
use App\Models\Service;
use Illuminate\Support\Facades\Gate;

class RestoreCatalogSeo
{
    public function handle(Service|PortfolioItem $service, Revision $revision, int $version): void
    {
        Gate::authorize('updateSeo', $service);
        $snapshot = $revision->snapshot ?? [];
        abort_unless($revision->revisionable_type === $service::class && (int) $revision->revisionable_id === $service->id
            && ($snapshot['kind'] ?? null) === 'seo' && is_array($snapshot['seo'] ?? null), 404);

        app(SaveCatalogSeo::class)->handle($service, array_replace($snapshot['seo'], ['content_version' => $version]));
    }
}
