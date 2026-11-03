<?php

namespace App\Http\Requests\Admin;

use App\Models\Redirect;
use App\Rules\RedirectSource;
use App\Rules\RedirectTarget;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $redirect = $this->route('redirect');

        return $redirect instanceof Redirect ? ($this->user()?->can('update', $redirect) ?? false)
            : ($this->user()?->can('create', Redirect::class) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $redirect = $this->route('redirect');

        return ['from_path' => ['bail', 'required', 'string', 'max:768', new RedirectSource,
            Rule::unique('redirects', 'from_path')->ignore($redirect instanceof Redirect ? $redirect->id : null)],
            'to_url' => ['nullable', 'required_unless:status_code,410', 'string', 'max:768', new RedirectTarget],
            'status_code' => ['required', 'integer', Rule::in([301, 302, 410])]];
    }
}
