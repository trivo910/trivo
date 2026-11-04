<?php

namespace App\Actions\Admin;

use App\Models\Page;
use App\Models\Revision;
use Illuminate\Support\Facades\Gate;

class RestorePageSeo
{
    public function handle(Page $page, Revision $revision, int $version): Page
    {
        Gate::authorize('updateSeo', $page);
        $snapshot = $revision->snapshot ?? [];
        abort_unless($revision->revisionable_type === Page::class && (int) $revision->revisionable_id === $page->id
            && ($snapshot['kind'] ?? null) === 'seo' && is_array($snapshot['seo'] ?? null), 404);

        return app(SavePageSeo::class)->handle($page, array_replace($snapshot['seo'], ['content_version' => $version]));
    }
}
