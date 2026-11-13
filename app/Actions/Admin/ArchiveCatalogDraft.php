<?php

namespace App\Actions\Admin;

use App\Models\Client;
use App\Models\Faq;
use App\Models\PortfolioItem;
use App\Models\Service;
use App\Models\Testimonial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ArchiveCatalogDraft
{
    public function handle(Service|PortfolioItem|Faq|Testimonial|Client $service, int $version, bool $restore = false): void
    {
        $ability = $restore ? 'restore' : 'delete';
        Gate::authorize($ability, $service);
        DB::transaction(function () use ($service, $version, $restore, $ability) {
            $service = $service->newQuery()->withTrashed()->lockForUpdate()->findOrFail($service->id);
            Gate::authorize($ability, $service);
            if ((int) $service->content_version !== $version) {
                throw ValidationException::withMessages(['content_version' => 'This content changed. Reload before continuing.']);
            }
            $service->increment('content_version');
            $restore ? $service->restore() : $service->delete();
            $prefix = match (true) {
                $service instanceof Client => 'client_',
                $service instanceof Testimonial => 'testimonial_',
                $service instanceof Faq => 'faq_',
                $service instanceof PortfolioItem => 'portfolio_',
                default => 'service_',
            };
            $event = $prefix.($restore ? 'draft_restored' : 'draft_archived');
            activity('content')->causedBy(auth()->user())->performedOn($service)->event($event)->log($event);
        });
    }
}
