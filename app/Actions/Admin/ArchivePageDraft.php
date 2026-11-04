<?php

namespace App\Actions\Admin;

use App\Models\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ArchivePageDraft
{
    public function handle(Page $page, int $version, bool $restore = false): void
    {
        $ability = $restore ? 'restore' : 'delete';
        Gate::authorize($ability, $page);
        DB::transaction(function () use ($page, $version, $restore, $ability) {
            $page = Page::withTrashed()->lockForUpdate()->findOrFail($page->id);
            Gate::authorize($ability, $page);
            if ((int) $page->content_version !== $version) {
                throw ValidationException::withMessages(['content_version' => 'This page changed. Reload before continuing.']);
            }
            if (! $restore && Page::withTrashed()->where('parent_id', $page->id)->exists()) {
                throw ValidationException::withMessages(['page' => 'This page has child pages and cannot be archived.']);
            }
            $page->increment('content_version');
            $restore ? $page->restore() : $page->delete();
            activity('content')->causedBy(auth()->user())->performedOn($page)
                ->event($restore ? 'page_draft_restored' : 'page_draft_archived')->log($restore ? 'page_draft_restored' : 'page_draft_archived');
        });
    }
}
