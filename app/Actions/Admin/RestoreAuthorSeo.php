<?php

namespace App\Actions\Admin;

use App\Models\Author;
use App\Models\Revision;
use Illuminate\Support\Facades\Gate;

class RestoreAuthorSeo
{
    public function handle(Author $author, Revision $revision, int $version): Author
    {
        Gate::authorize('updateSeo', $author);
        $snapshot = $revision->snapshot ?? [];
        abort_unless($revision->revisionable_type === Author::class && (int) $revision->revisionable_id === $author->id && ($snapshot['kind'] ?? null) === 'seo' && is_array($snapshot['seo'] ?? null), 404);

        return app(SaveAuthorSeo::class)->handle($author, array_replace($snapshot['seo'], ['content_version' => $version]));
    }
}
