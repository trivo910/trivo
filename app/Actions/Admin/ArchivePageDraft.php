<?php

namespace App\Actions\Admin;

use App\Models\Page;
use App\Services\Content\ContentPaths;
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
            if ($restore) {
                $path = $page->archived_path;
                if (! is_string($path) || ! app(ContentPaths::class)->available($path, $page->id, 'pages')) {
                    throw ValidationException::withMessages(['path' => 'This path is now in use. Resolve the path conflict before restoring this draft.']);
                }
                $page->forceFill(['path' => $path, 'archived_path' => null, 'content_version' => $page->content_version + 1])->save();
                $page->restore();
            } else {
                if (Page::withTrashed()->where('parent_id', $page->id)->exists()) {
                    throw ValidationException::withMessages(['page' => 'This page has child pages and cannot be archived.']);
                }
                $page->forceFill(['archived_path' => $page->path, 'path' => null,
                    'content_version' => $page->content_version + 1])->save();
                $page->delete();
            }
            activity('content')->causedBy(auth()->user())->performedOn($page)
                ->event($restore ? 'page_draft_restored' : 'page_draft_archived')->log($restore ? 'page_draft_restored' : 'page_draft_archived');
        });
    }
}
