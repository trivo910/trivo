<?php

namespace App\Http\Requests\Admin;

use App\Models\Post;
use App\Services\Content\BlogPostSeo;
use Illuminate\Foundation\Http\FormRequest;

class BlogPostSeoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post instanceof Post && ($this->user()?->can('updateSeo', $post) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rules = ['content_version' => ['required', 'integer', 'min:1'], 'sitemap_include' => ['required', 'boolean']];
        foreach (BlogPostSeo::FIELDS as $field) {
            $rules[$field] ??= ['nullable'];
        }

        return $rules;
    }

    protected function passedValidation(): void
    {
        app(BlogPostSeo::class)->validate($this->validated(), 'post');
    }
}
