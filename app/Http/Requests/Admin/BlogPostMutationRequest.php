<?php

namespace App\Http\Requests\Admin;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;

class BlogPostMutationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = Post::query()->withTrashed()->findOrFail((int) $this->route('post'));
        $ability = match ($this->route()?->getName()) {
            'admin.posts.restore' => 'restore', 'admin.posts.revision' => 'update', default => 'delete'
        };

        return $this->user()?->can($ability, $post) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['content_version' => ['required', 'integer', 'min:1']];
    }
}
