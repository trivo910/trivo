<?php

namespace App\Http\Requests\Admin;

use App\Models\Author;
use App\Services\Content\BlogPostSeo;
use Illuminate\Foundation\Http\FormRequest;

class AuthorSeoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $author = $this->route('author');

        return $author instanceof Author && ($this->user()?->can('updateSeo', $author) ?? false);
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
        app(BlogPostSeo::class)->validate($this->validated(), 'author');
    }
}
