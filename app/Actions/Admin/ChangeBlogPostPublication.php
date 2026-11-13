<?php

namespace App\Actions\Admin;

use App\Models\Post;

class ChangeBlogPostPublication
{
    /** @param array<string, mixed> $values */
    public function handle(Post $post, array $values): void
    {
        app(ChangeContentPublication::class)->handle($post, $values);
    }
}
