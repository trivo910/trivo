<?php

namespace App\Http\Requests\Admin;

use App\Models\Menu;
use Illuminate\Foundation\Http\FormRequest;

class MenuDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $menu = $this->route('menu');

        return $menu instanceof Menu ? ($this->user()?->can('update', $menu) ?? false) : ($this->user()?->can('create', Menu::class) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:150'], 'location' => ['required', 'string', 'max:64'],
            'content_version' => [$this->route('menu') ? 'required' : 'nullable', 'integer', 'min:1']];
    }
}
