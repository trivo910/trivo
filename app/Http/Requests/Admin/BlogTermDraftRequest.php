<?php

namespace App\Http\Requests\Admin;

use App\Services\Content\BlogTerms;
use Illuminate\Foundation\Http\FormRequest;

class BlogTermDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $terms = app(BlogTerms::class);
        $kind = (string) $this->route('kind');
        $term = $this->route('term') ? $terms->find($kind, (int) $this->route('term')) : null;

        return $term ? ($this->user()?->can('update', $term) ?? false) : ($this->user()?->can('create', $terms->model($kind)::class) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'slug' => ['required', 'string', 'max:255'], 'path' => ['required', 'string', 'max:768'],
            'description' => ['nullable', 'string', 'max:10000'], 'parent_id' => ['nullable', 'integer', 'min:1'],
            'content_version' => [$this->route('term') ? 'required' : 'nullable', 'integer', 'min:1']];
    }
}
