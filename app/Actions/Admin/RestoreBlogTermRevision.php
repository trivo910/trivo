<?php

namespace App\Actions\Admin;

use App\Models\Category;
use App\Models\Revision;
use App\Models\Tag;
use Illuminate\Support\Facades\Gate;

class RestoreBlogTermRevision
{
    public function handle(Category|Tag $term, Revision $revision, int $version): void
    {
        Gate::authorize('update', $term);
        $snapshot = $revision->snapshot ?? [];
        abort_unless($revision->revisionable_type === $term::class && (int) $revision->revisionable_id === $term->id
            && ($snapshot['kind'] ?? null) === 'taxonomy' && is_array($snapshot['content'] ?? null), 404);
        app(SaveBlogTerm::class)->handle($term, array_replace($snapshot['content'], ['path' => $term->path, 'content_version' => $version]));
    }
}
