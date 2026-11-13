<?php

namespace App\Http\Requests\Admin;

use App\Models\Menu;
use Illuminate\Foundation\Http\FormRequest;

class MenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $menu = $this->route('menu');
        $ability = in_array($this->route()?->getName(), ['admin.menus.items.archive', 'admin.menus.items.restore'], true) ? 'archiveItem' : 'update';

        return $menu instanceof Menu && ($this->user()?->can($ability, $menu) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rules = ['content_version' => ['required', 'integer', 'min:1']];
        if (in_array($this->route()?->getName(), ['admin.menus.items.archive', 'admin.menus.items.restore'], true)) {
            return $rules;
        }

        return $rules + ['label' => ['required', 'string', 'max:150'], 'url' => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'integer', 'min:1'], 'sort' => ['required', 'integer', 'between:0,10000'],
            'target' => ['nullable', 'in:_self,_blank']];
    }
}
