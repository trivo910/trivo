<?php

namespace App\Actions\Admin;

use App\Models\Post;
use App\Models\Revision;
use App\Services\Content\BlogPostSeo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SaveBlogPostSeo
{
    /** @param array<string, mixed> $values */
    public function handle(Post $post, array $values): Post
    {
        Gate::authorize('updateSeo', $post);

        return DB::transaction(function () use ($post, $values) {
            $post = Post::query()->lockForUpdate()->findOrFail($post->id);
            Gate::authorize('updateSeo', $post);
            if ((int) ($values['content_version'] ?? 0) !== (int) $post->content_version) {
                throw ValidationException::withMessages(['content_version' => 'This post changed since you opened it. Reload before saving SEO.']);
            }
            $data = app(BlogPostSeo::class)->validate($values, 'post');
            app(BlogPostSeo::class)->lockImages($data);
            $post->fill($data);
            $post->content_version++;
            $post->save();
            Revision::query()->create(['revisionable_type' => Post::class, 'revisionable_id' => $post->id, 'user_id' => auth()->id(), 'snapshot' => ['kind' => 'seo', 'seo' => $data, 'content_version' => $post->content_version]]);
            activity('content')->causedBy(auth()->user())->performedOn($post)->event('post_seo_saved')->log('post_seo_saved');

            return $post;
        });
    }
}
