<?php

namespace App\Http\Requests\Admin;

use App\Models\Redirect;
use Illuminate\Foundation\Http\FormRequest;

class RedirectImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Redirect::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['csv' => ['required', 'file', 'mimes:csv,txt', 'max:1024']];
    }
}
