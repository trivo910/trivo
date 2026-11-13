<?php

namespace App\Actions\Admin;

use App\Models\Author;
use App\Models\Revision;
use App\Services\Content\BlogPostSeo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SaveAuthorSeo
{
    /** @param array<string, mixed> $values */
    public function handle(Author $author, array $values): Author
    {
        Gate::authorize('updateSeo', $author);

        return DB::transaction(function () use ($author, $values) {
            $author = Author::query()->lockForUpdate()->findOrFail($author->id);
            Gate::authorize('updateSeo', $author);
            if ((int) ($values['content_version'] ?? 0) !== (int) $author->content_version) {
                throw ValidationException::withMessages(['content_version' => 'This author changed since you opened it. Reload before saving SEO.']);
            }
            $data = app(BlogPostSeo::class)->validate($values, 'author');
            app(BlogPostSeo::class)->lockImages($data);
            $author->fill($data);
            $author->content_version++;
            $author->save();
            Revision::query()->create(['revisionable_type' => Author::class, 'revisionable_id' => $author->id, 'user_id' => auth()->id(), 'snapshot' => ['kind' => 'seo', 'seo' => $data, 'content_version' => $author->content_version]]);
            activity('content')->causedBy(auth()->user())->performedOn($author)->event('author_seo_saved')->log('author_seo_saved');

            return $author;
        });
    }
}
