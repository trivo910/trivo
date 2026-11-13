<?php

namespace App\Http\Requests\Admin;

use App\Models\Author;
use Illuminate\Foundation\Http\FormRequest;

class AuthorProfileDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $author = $this->route('author') ? Author::query()->findOrFail((int) $this->route('author')) : null;

        return $author ? ($this->user()?->can('update', $author) ?? false) : ($this->user()?->can('create', Author::class) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'path' => ['required', 'string', 'max:768'], 'bio' => ['nullable', 'string', 'max:10000'],
            'avatar_id' => ['nullable', 'integer', 'min:1'], 'socials' => ['nullable', 'array'], 'socials.*' => ['nullable', 'string', 'max:2048'],
            'content_version' => [$this->route('author') ? 'required' : 'nullable', 'integer', 'min:1']];
    }
}
