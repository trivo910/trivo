<?php

namespace App\Http\Requests\Admin;

use App\Models\Author;
use Illuminate\Foundation\Http\FormRequest;

class AuthorProfileMutationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $author = Author::query()->withTrashed()->findOrFail((int) $this->route('author'));
        $ability = match ($this->route()?->getName()) {
            'admin.authors.restore' => 'restore', 'admin.authors.revision' => 'update', default => 'delete',
        };

        return $this->user()?->can($ability, $author) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['content_version' => ['required', 'integer', 'min:1']];
    }
}
