<?php

namespace App\Actions\Admin;

use App\Models\Category;
use App\Models\Tag;
use App\Services\Content\BlogTerms;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ArchiveBlogTerm
{
    public function handle(Category|Tag $term, int $version, bool $restore = false): void
    {
        $ability = $restore ? 'restore' : 'delete';
        Gate::authorize($ability, $term);
        try {
            Cache::lock('content-path-writes', 30)->block(5, fn () => DB::transaction(function () use ($term, $version, $restore, $ability) {
                $term = $term->newQuery()->withTrashed()->lockForUpdate()->findOrFail($term->id);
                Gate::authorize($ability, $term);
                if ($term->content_version !== $version) {
                    throw ValidationException::withMessages(['content_version' => 'This term changed. Reload before continuing.']);
                }
                if (! $restore && $term instanceof Category && Category::query()->where('parent_id', $term->id)->exists()) {
                    throw ValidationException::withMessages(['parent_id' => 'Move or archive active child categories first.']);
                }
                if ($restore) {
                    app(BlogTerms::class)->validate($term, $term->only(['name', 'slug', 'path', 'description', 'parent_id']));
                }
                $term->increment('content_version');
                $restore ? $term->restore() : $term->delete();
                $event = $restore ? 'blog_term_restored' : 'blog_term_archived';
                activity('content')->causedBy(auth()->user())->performedOn($term)->event($event)->log($event);
            }));
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages(['path' => 'Another content change is in progress. Try again shortly.']);
        }
    }
}
