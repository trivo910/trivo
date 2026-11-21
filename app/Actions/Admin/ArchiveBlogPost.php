<?php

namespace App\Actions\Admin;

use App\Models\Post;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ArchiveBlogPost
{
    public function handle(Post $post, int $version, bool $restore = false): void
    {
        $ability = $restore ? 'restore' : 'delete';
        Gate::authorize($ability, $post);
        try {
            Cache::lock('content-path-writes', 30)->block(5, fn () => DB::transaction(function () use ($post, $version, $restore, $ability) {
                $post = Post::query()->withTrashed()->lockForUpdate()->findOrFail($post->id);
                Gate::authorize($ability, $post);
                if ($post->content_version !== $version) {
                    throw ValidationException::withMessages(['content_version' => 'This post changed. Reload before continuing.']);
                }
                $post->increment('content_version');
                if ($restore) {
                    $post->forceFill(['status' => 'draft', 'published_at' => null, 'publication_error' => null]);
                }
                $restore ? $post->restore() : $post->delete();
                $event = $restore ? 'blog_post_draft_restored' : 'blog_post_draft_archived';
                activity('content')->causedBy(auth()->user())->performedOn($post)->event($event)->log($event);
            }));
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages(['path' => 'Another content change is in progress. Try again shortly.']);
        }
    }
}
