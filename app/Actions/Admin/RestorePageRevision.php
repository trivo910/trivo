<?php

namespace App\Actions\Admin;

use App\Models\Page;
use App\Models\Revision;
use Illuminate\Support\Facades\Gate;

class RestorePageRevision
{
    public function handle(Page $page, Revision $revision, int $version): Page
    {
        Gate::authorize('update', $page);
        abort_unless($revision->revisionable_type === Page::class && (int) $revision->revisionable_id === $page->id, 404);
        $snapshot = $revision->snapshot ?? [];
        abort_unless(in_array($snapshot['kind'] ?? null, [null, 'content'], true), 404);

        // Restore content only; keep the current canonical path, template and SEO metadata.
        return app(SavePageDraft::class)->handle(['title' => $snapshot['title'] ?? null, 'path' => $page->path,
            'blocks' => $snapshot['blocks'] ?? [], 'content_version' => $version], $page);
    }
}
