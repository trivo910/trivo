<?php

namespace App\Actions\Admin;

use App\Models\Author;
use App\Models\Revision;
use App\Services\Content\AuthorProfiles;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SaveAuthorProfile
{
    /** @param array<string, mixed> $values */
    public function handle(Author $author, array $values): Author
    {
        Gate::authorize($author->exists ? 'update' : 'create', $author->exists ? $author : Author::class);
        try {
            return Cache::lock('content-path-writes', 30)->block(5, fn () => DB::transaction(function () use ($author, $values) {
                if ($author->exists) {
                    $author = Author::query()->lockForUpdate()->findOrFail($author->id);
                    Gate::authorize('update', $author);
                    if ((int) ($values['content_version'] ?? 0) !== $author->content_version) {
                        throw ValidationException::withMessages(['content_version' => 'This author changed. Reload before saving.']);
                    }
                }
                $data = app(AuthorProfiles::class)->validate($author, $values);
                $author->fill($data);
                $author->content_version = $author->exists ? $author->content_version + 1 : 1;
                $author->save();
                Revision::query()->create(['revisionable_type' => Author::class, 'revisionable_id' => $author->id, 'user_id' => auth()->id(),
                    'snapshot' => ['kind' => 'author_profile', 'content' => $data, 'content_version' => $author->content_version]]);
                activity('content')->causedBy(auth()->user())->performedOn($author)->event('author_profile_saved')->log('author_profile_saved');

                return $author;
            }));
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages(['path' => 'Another content change is in progress. Try again shortly.']);
        }
    }
}
