<?php

namespace App\Http\Requests\Admin;

use App\Services\Content\BlogTerms;
use Illuminate\Foundation\Http\FormRequest;

class BlogTermIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', app(BlogTerms::class)->model((string) $this->route('kind'))::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['q' => ['nullable', 'string', 'max:120'], 'state' => ['nullable', 'in:active,archived']];
    }
}
