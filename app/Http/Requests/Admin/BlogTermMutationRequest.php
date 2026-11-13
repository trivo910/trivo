<?php

namespace App\Http\Requests\Admin;

use App\Services\Content\BlogTerms;
use Illuminate\Foundation\Http\FormRequest;

class BlogTermMutationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $term = app(BlogTerms::class)->find((string) $this->route('kind'), (int) $this->route('term'), true);
        $ability = match ($this->route()?->getName()) {
            'admin.blog.terms.restore' => 'restore', 'admin.blog.terms.revision' => 'update', default => 'delete',
        };

        return $this->user()?->can($ability, $term) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['content_version' => ['required', 'integer', 'min:1']];
    }
}
