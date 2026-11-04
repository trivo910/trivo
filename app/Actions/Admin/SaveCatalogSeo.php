<?php

namespace App\Actions\Admin;

use App\Models\PortfolioItem;
use App\Models\Revision;
use App\Models\Service;
use App\Services\Content\ContentSeo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SaveCatalogSeo
{
    /** @param array<string, mixed> $values */
    public function handle(Service|PortfolioItem $service, array $values): void
    {
        Gate::authorize('updateSeo', $service);

        DB::transaction(function () use ($service, $values) {
            $service = $service->newQuery()->lockForUpdate()->findOrFail($service->id);
            Gate::authorize('updateSeo', $service);
            if ((int) ($values['content_version'] ?? 0) !== (int) $service->content_version) {
                throw ValidationException::withMessages(['content_version' => 'This content changed. Reload before saving SEO.']);
            }
            $data = app(ContentSeo::class)->validate($values, $service instanceof PortfolioItem ? 'portfolio' : 'service');
            app(ContentSeo::class)->lockImages($data);
            $service->fill($data);
            $service->content_version++;
            $service->save();
            Revision::query()->create(['revisionable_type' => $service::class, 'revisionable_id' => $service->id, 'user_id' => auth()->id(),
                'snapshot' => ['kind' => 'seo', 'seo' => $data, 'content_version' => $service->content_version]]);
            $event = $service instanceof PortfolioItem ? 'portfolio_seo_saved' : 'service_seo_saved';
            activity('content')->causedBy(auth()->user())->performedOn($service)->event($event)->log($event);
        });
    }
}
