<?php

namespace App\Http\Requests\Admin;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;

class BlogPostDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post') ? Post::query()->findOrFail((int) $this->route('post')) : null;

        return $post ? ($this->user()?->can('update', $post) ?? false) : ($this->user()?->can('create', Post::class) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['title' => ['required', 'string', 'max:255'], 'slug' => ['required', 'string', 'max:255'], 'path' => ['required', 'string', 'max:768'],
            'excerpt' => ['nullable', 'string', 'max:1000'], 'body' => ['required', 'string', 'max:100000'], 'author_id' => ['nullable', 'integer', 'min:1'],
            'category_id' => ['nullable', 'integer', 'min:1'], 'tag_ids' => ['present', 'array', 'max:20'], 'tag_ids.*' => ['integer', 'min:1', 'distinct'],
            'featured_media_id' => ['nullable', 'integer', 'min:1'], 'focus_keyword' => ['nullable', 'string', 'max:191'], 'secondary_keywords' => ['present', 'array', 'max:4'], 'secondary_keywords.*' => ['nullable', 'string', 'max:191'],
            'seo_title' => ['nullable', 'string', 'max:255'], 'seo_description' => ['nullable', 'string', 'max:500'],
            'is_cornerstone' => ['required', 'boolean'], 'show_toc' => ['required', 'boolean'],
            'content_version' => [$this->route('post') ? 'required' : 'nullable', 'integer', 'min:1']];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_cornerstone' => $this->boolean('is_cornerstone'), 'show_toc' => $this->boolean('show_toc')]);
        if (! $this->has('secondary_keywords_text')) {
            return;
        }
        $values = preg_split('/[,\r\n]+/', (string) $this->input('secondary_keywords_text'), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $this->merge(['secondary_keywords' => $values]);
    }
}
