<?php

namespace App\Actions\Admin;

use App\Models\Author;
use App\Models\Revision;
use Illuminate\Support\Facades\Gate;

class RestoreAuthorProfileRevision
{
    public function handle(Author $author, Revision $revision, int $version): void
    {
        Gate::authorize('update', $author);
        $snapshot = $revision->snapshot ?? [];
        abort_unless($revision->revisionable_type === Author::class && (int) $revision->revisionable_id === $author->id
            && ($snapshot['kind'] ?? null) === 'author_profile' && is_array($snapshot['content'] ?? null), 404);
        app(SaveAuthorProfile::class)->handle($author, array_replace($snapshot['content'], ['path' => $author->path, 'content_version' => $version]));
    }
}
