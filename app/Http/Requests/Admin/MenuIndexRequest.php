<?php

namespace App\Http\Requests\Admin;

use App\Models\Menu;

class MenuIndexRequest extends PageIndexRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Menu::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['state' => ['nullable', 'in:active,archived'], 'q' => ['nullable', 'string', 'max:120']];
    }
}
