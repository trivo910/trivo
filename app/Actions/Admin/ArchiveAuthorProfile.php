<?php

namespace App\Actions\Admin;

use App\Models\Author;
use App\Services\Content\AuthorProfiles;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ArchiveAuthorProfile
{
    public function handle(Author $author, int $version, bool $restore = false): void
    {
        $ability = $restore ? 'restore' : 'delete';
        Gate::authorize($ability, $author);
        try {
            Cache::lock('content-path-writes', 30)->block(5, fn () => DB::transaction(function () use ($author, $version, $restore, $ability) {
                $author = Author::query()->withTrashed()->lockForUpdate()->findOrFail($author->id);
                Gate::authorize($ability, $author);
                if ($author->content_version !== $version) {
                    throw ValidationException::withMessages(['content_version' => 'This author changed. Reload before continuing.']);
                }
                if ($restore) {
                    app(AuthorProfiles::class)->validate($author, $author->only(['name', 'path', 'bio', 'avatar_id', 'socials']));
                }
                $author->increment('content_version');
                $restore ? $author->restore() : $author->delete();
                $event = $restore ? 'author_profile_restored' : 'author_profile_archived';
                activity('content')->causedBy(auth()->user())->performedOn($author)->event($event)->log($event);
            }));
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages(['path' => 'Another content change is in progress. Try again shortly.']);
        }
    }
}
