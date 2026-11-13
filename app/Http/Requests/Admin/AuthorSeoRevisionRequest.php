<?php

namespace App\Http\Requests\Admin;

use App\Models\Author;
use Illuminate\Foundation\Http\FormRequest;

class AuthorSeoRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $author = $this->route('author');

        return $author instanceof Author && ($this->user()?->can('updateSeo', $author) ?? false);
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['content_version' => ['required', 'integer', 'min:1']];
    }
}
