<?php

namespace App\Actions\Admin;

use App\Models\Post;
use App\Models\Revision;
use Illuminate\Support\Facades\Gate;

class RestoreBlogPostRevision
{
    public function handle(Post $post, Revision $revision, int $version): void
    {
        Gate::authorize('update', $post);
        $snapshot = $revision->snapshot ?? [];
        abort_unless($revision->revisionable_type === Post::class && (int) $revision->revisionable_id === $post->id
            && ($snapshot['kind'] ?? null) === 'post_draft' && is_array($snapshot['content'] ?? null), 404);
        app(SaveBlogPost::class)->handle($post, array_replace($snapshot['content'], ['path' => $post->path, 'content_version' => $version]));
    }
}
