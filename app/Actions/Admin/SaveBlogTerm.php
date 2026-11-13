<?php

namespace App\Actions\Admin;

use App\Models\Category;
use App\Models\Revision;
use App\Models\Tag;
use App\Services\Content\BlogTerms;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SaveBlogTerm
{
    /** @param array<string, mixed> $values */
    public function handle(Category|Tag $term, array $values): Category|Tag
    {
        Gate::authorize($term->exists ? 'update' : 'create', $term->exists ? $term : $term::class);
        try {
            return Cache::lock('content-path-writes', 30)->block(5, fn () => DB::transaction(function () use ($term, $values) {
                if ($term->exists) {
                    $term = $term->newQuery()->lockForUpdate()->findOrFail($term->id);
                    Gate::authorize('update', $term);
                    if ((int) ($values['content_version'] ?? 0) !== $term->content_version) {
                        throw ValidationException::withMessages(['content_version' => 'This term changed. Reload before saving.']);
                    }
                }
                $data = app(BlogTerms::class)->validate($term, $values);
                $term->fill($data);
                $term->content_version = $term->exists ? $term->content_version + 1 : 1;
                $term->save();
                Revision::query()->create(['revisionable_type' => $term::class, 'revisionable_id' => $term->id, 'user_id' => auth()->id(),
                    'snapshot' => ['kind' => 'taxonomy', 'content' => $data, 'content_version' => $term->content_version]]);
                activity('content')->causedBy(auth()->user())->performedOn($term)->event('blog_term_saved')->log('blog_term_saved');

                return $term;
            }));
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages(['path' => 'Another content change is in progress. Try again shortly.']);
        }
    }
}
