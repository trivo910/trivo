<?php

namespace App\Http\Requests\Admin;

use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;

class PageMutationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $page = $this->route('page');
        $ability = match ($this->route()?->getName()) {
            'admin.pages.restore' => 'restore', 'admin.pages.revision' => 'update', default => 'delete',
        };

        return $page instanceof Page && ($this->user()?->can($ability, $page) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['content_version' => ['required', 'integer', 'min:1']];
    }
}
