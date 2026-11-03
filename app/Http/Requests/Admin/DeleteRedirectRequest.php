<?php

namespace App\Http\Requests\Admin;

use App\Models\Redirect;
use Illuminate\Foundation\Http\FormRequest;

class DeleteRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $redirect = $this->route('redirect');

        return $redirect instanceof Redirect && ($this->user()?->can('delete', $redirect) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
