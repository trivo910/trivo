<?php

namespace App\Actions\Admin;

use App\Models\Page;
use App\Models\Revision;
use App\Services\Content\ContentDrafts;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SavePageDraft
{
    /** @param array<string, mixed> $values */
    public function handle(array $values, ?Page $page = null): Page
    {
        Gate::authorize($page ? 'update' : 'create', $page ?? Page::class);
        try {
            return Cache::lock('content-path-writes', 30)->block(5, fn () => DB::transaction(function () use ($page, $values) {
                if ($page) {
                    $page = Page::query()->lockForUpdate()->findOrFail($page->id);
                    Gate::authorize('update', $page);
                    if ((int) ($values['content_version'] ?? 0) !== (int) $page->content_version) {
                        throw ValidationException::withMessages(['content_version' => 'This page changed since you opened it. Reload before saving.']);
                    }
                }
                $data = app(ContentDrafts::class)->validate($values, $page);
                app(ContentDrafts::class)->lockImages($data['blocks']);
                $page ??= new Page(['status' => 'draft', 'template' => 'page']);
                $page->fill(['title' => $data['title'], 'path' => $data['path'], 'blocks' => $data['blocks'],
                    'content_version' => $page->exists ? $page->content_version + 1 : 1]);
                $page->save();
                Revision::query()->create(['revisionable_type' => Page::class, 'revisionable_id' => $page->id,
                    'user_id' => auth()->id(), 'snapshot' => ['kind' => 'content', 'title' => $page->title, 'path' => $page->path,
                        'blocks' => $page->blocks, 'content_version' => $page->content_version]]);
                activity('content')->causedBy(auth()->user())->performedOn($page)->event('page_draft_saved')->log('page_draft_saved');

                return $page;
            }));
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages(['path' => 'Another content save is in progress. Try again shortly.']);
        }
    }
}
