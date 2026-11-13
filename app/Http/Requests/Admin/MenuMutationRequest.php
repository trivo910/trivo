<?php

namespace App\Http\Requests\Admin;

use App\Models\Menu;
use Illuminate\Foundation\Http\FormRequest;

class MenuMutationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $menu = $this->route('menu');
        $ability = $this->routeIs('admin.menus.restore') ? 'restore' : 'delete';

        return $menu instanceof Menu && ($this->user()?->can($ability, $menu) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['content_version' => ['required', 'integer', 'min:1']];
    }
}
