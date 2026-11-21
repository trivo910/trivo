<?php

namespace App\Actions\Admin;

use App\Models\Post;
use App\Models\Revision;
use App\Services\Content\BlogPosts;
use App\Services\Content\PostReadingTime;
use App\Services\Content\PostSeoAnalysis;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SaveBlogPost
{
    /** @param array<string, mixed> $values */
    public function handle(Post $post, array $values): Post
    {
        Gate::authorize($post->exists ? 'update' : 'create', $post->exists ? $post : Post::class);
        try {
            return Cache::lock('content-path-writes', 30)->block(5, fn () => DB::transaction(function () use ($post, $values) {
                if ($post->exists) {
                    $post = Post::query()->lockForUpdate()->findOrFail($post->id);
                    Gate::authorize('update', $post);
                    if ((int) ($values['content_version'] ?? 0) !== $post->content_version) {
                        throw ValidationException::withMessages(['content_version' => 'This post changed. Reload before saving.']);
                    }
                }
                $validated = app(BlogPosts::class)->validate($post, $values);
                $post->fill($validated['post']);
                $post->forceFill(['body_format' => 'html']);
                if (! $post->exists) {
                    $post->status = 'draft';
                }
                $post->reading_time = app(PostReadingTime::class)->minutes($post->body);
                $analysis = app(PostSeoAnalysis::class)->analyze($post);
                $post->seo_score = $analysis['seo_score'];
                $post->readability_score = $analysis['readability_score'];
                $post->seo_analysis = $analysis['analysis'];
                $post->content_version = $post->exists ? $post->content_version + 1 : 1;
                $post->save();
                $post->tags()->sync($validated['tag_ids']);
                Revision::query()->create(['revisionable_type' => Post::class, 'revisionable_id' => $post->id, 'user_id' => auth()->id(),
                    'snapshot' => ['kind' => 'post_draft', 'content' => $validated['post'] + ['tag_ids' => $validated['tag_ids']], 'content_version' => $post->content_version]]);
                activity('content')->causedBy(auth()->user())->performedOn($post)->event('blog_post_draft_saved')->log('blog_post_draft_saved');

                return $post;
            }));
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages(['path' => 'Another content change is in progress. Try again shortly.']);
        }
    }
}
