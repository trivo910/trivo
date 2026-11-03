<?php

namespace App\Http\Requests\Admin;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MediaIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Media::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['q' => ['nullable', 'string', 'max:120'], 'folder' => ['nullable', 'string', 'max:80'],
            'state' => ['nullable', Rule::in(['active', 'archived', 'queued', 'processing', 'failed'])]];
    }
}
