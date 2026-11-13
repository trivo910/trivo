<?php

namespace App\Actions\Admin;

use App\Models\Post;
use App\Models\Revision;
use Illuminate\Support\Facades\Gate;

class RestoreBlogPostSeo
{
    public function handle(Post $post, Revision $revision, int $version): Post
    {
        Gate::authorize('updateSeo', $post);
        $snapshot = $revision->snapshot ?? [];
        abort_unless($revision->revisionable_type === Post::class && (int) $revision->revisionable_id === $post->id && ($snapshot['kind'] ?? null) === 'seo' && is_array($snapshot['seo'] ?? null), 404);

        return app(SaveBlogPostSeo::class)->handle($post, array_replace($snapshot['seo'], ['content_version' => $version]));
    }
}
