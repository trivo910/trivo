<?php

namespace App\Actions\Admin;

use App\Models\Client;
use App\Models\Faq;
use App\Models\PortfolioItem;
use App\Models\Revision;
use App\Models\Service;
use App\Models\Testimonial;
use App\Services\Content\ContentDrafts;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SaveCatalogDraft
{
    /** @param array<string, mixed> $values */
    public function handle(Service|PortfolioItem|Faq|Testimonial|Client $service, array $values): void
    {
        Gate::authorize($service->exists ? 'update' : 'create', $service->exists ? $service : $service::class);
        try {
            Cache::lock('content-path-writes', 30)->block(5, fn () => DB::transaction(function () use ($service, $values) {
                if ($service->exists) {
                    $service = $service->newQuery()->lockForUpdate()->findOrFail($service->id);
                    Gate::authorize('update', $service);
                    if ((int) ($values['content_version'] ?? 0) !== (int) $service->content_version) {
                        throw ValidationException::withMessages(['content_version' => 'This content changed. Reload before saving.']);
                    }
                }
                $extra = Validator::make($values, ['sort' => ['required', 'integer', 'between:0,10000'], 'media_id' => ['nullable', 'integer', 'min:1']])->validate();
                $data = app(ContentDrafts::class)->validate($values, $service);
                $imageBlocks = $data['blocks'];
                if (! empty($extra['media_id'])) {
                    $imageBlocks[] = ['type' => 'image', 'media_id' => $extra['media_id']];
                }
                app(ContentDrafts::class)->lockImages($imageBlocks);
                $service->fill(['title' => $data['title'], 'path' => $data['path'], 'blocks' => $data['blocks'], 'sort' => (int) $extra['sort'],
                    'media_id' => ! empty($extra['media_id']) ? (int) $extra['media_id'] : null, 'content_version' => $service->exists ? $service->content_version + 1 : 1]);
                $service->save();
                Revision::query()->create(['revisionable_type' => $service::class, 'revisionable_id' => $service->id, 'user_id' => auth()->id(),
                    'snapshot' => ['kind' => 'content', 'title' => $service->title, 'path' => $service->path, 'blocks' => $service->blocks,
                        'media_id' => $service->media_id, 'sort' => $service->sort, 'content_version' => $service->content_version]]);
                $event = match (true) {
                    $service instanceof Client => 'client_draft_saved',
                    $service instanceof Testimonial => 'testimonial_draft_saved',
                    $service instanceof Faq => 'faq_draft_saved',
                    $service instanceof PortfolioItem => 'portfolio_draft_saved',
                    default => 'service_draft_saved',
                };
                activity('content')->causedBy(auth()->user())->performedOn($service)->event($event)->log($event);
            }));
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages(['path' => 'Another content change is in progress. Try again shortly.']);
        }
    }
}
