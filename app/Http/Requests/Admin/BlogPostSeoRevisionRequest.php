<?php

namespace App\Http\Requests\Admin;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;

class BlogPostSeoRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post instanceof Post && ($this->user()?->can('updateSeo', $post) ?? false);
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['content_version' => ['required', 'integer', 'min:1']];
    }
}
