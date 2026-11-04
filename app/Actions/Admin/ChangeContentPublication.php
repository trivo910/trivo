<?php

namespace App\Actions\Admin;

use App\Models\Page;
use App\Models\PortfolioItem;
use App\Models\Service;
use App\Services\Content\ContentPublication;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ChangeContentPublication
{
    /** @param array<string, mixed> $values */
    public function handle(Page|Service|PortfolioItem $page, array $values): void
    {
        $ability = ($values['operation'] ?? null) === 'cancel' ? 'cancelPublication' : 'publish';
        Gate::authorize($ability, $page);
        try {
            Cache::lock('content-path-writes', 30)->block(5, fn () => DB::transaction(function () use ($page, $values, $ability) {
                $page = $page->newQuery()->lockForUpdate()->findOrFail($page->id);
                Gate::authorize($ability, $page);
                $data = Validator::make($values, ['operation' => ['required', Rule::in(['publish', 'schedule', 'cancel'])],
                    'content_version' => ['required', 'integer', 'min:1'], 'scheduled_for' => [Rule::excludeIf(($values['operation'] ?? '') !== 'schedule'),
                        'required', 'date_format:Y-m-d\TH:i', 'after:now']])->validate();
                if ((int) $data['content_version'] !== (int) $page->content_version) {
                    throw ValidationException::withMessages(['content_version' => 'This content changed. Reload before changing publication.']);
                }
                $operation = $data['operation'];
                if ($operation !== 'cancel') {
                    app(ContentPublication::class)->check($page);
                    $page->publication_locked_at ??= now();
                }
                $page->status = match ($operation) {
                    'publish' => 'published', 'schedule' => 'scheduled', default => 'draft'
                };
                $page->published_at = match ($operation) {
                    'publish' => now(), 'schedule' => Carbon::createFromFormat('!Y-m-d\TH:i', $data['scheduled_for'], 'UTC'), default => null
                };
                $page->publication_error = null;
                $page->content_version++;
                $page->save();
                app(ContentPublication::class)->record($page, app(ContentPublication::class)->context($page).'_'.$operation, auth()->id());
            }));
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages(['publication' => 'Another content change is in progress. Try again shortly.']);
        }
    }
}
