<?php

namespace App\Http\Requests\Admin;

use App\Services\Admin\AdminAuthenticator;
use Illuminate\Foundation\Http\FormRequest;

class TwoFactorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(AdminAuthenticator::class)->pending($this) !== null;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['code' => ['required', 'string', 'min:6', 'max:64', 'regex:/^[A-Za-z0-9-]+$/D']];
    }
}
