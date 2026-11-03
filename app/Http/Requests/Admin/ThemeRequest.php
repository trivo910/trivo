<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ThemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('updatePreferences', $this->user()) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['theme' => ['required', Rule::in(['system', 'light', 'dark'])],
            'return_to' => ['required', Rule::in(['admin.dashboard', 'admin.styleguide', 'admin.activity'])]];
    }
}
