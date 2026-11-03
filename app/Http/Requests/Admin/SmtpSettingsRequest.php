<?php

namespace App\Http\Requests\Admin;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SmtpSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('updateMail', Setting::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'host' => ['required', 'string', 'max:253', 'regex:/\A[a-zA-Z0-9][a-zA-Z0-9.:-]*\z/D'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'encryption' => ['required', Rule::in(app()->isProduction() ? ['tls', 'ssl'] : ['tls', 'ssl', 'none'])],
            'username' => ['nullable', 'string', 'max:254', 'not_regex:/[\r\n]/'],
            'password' => ['nullable', 'string', 'max:2048'], 'clear_password' => ['sometimes', 'boolean'],
            'from_email' => ['required', 'email:rfc', 'max:254'],
            'from_name' => ['required', 'string', 'max:100', 'not_regex:/[\r\n]/'],
            'reply_to' => ['nullable', 'email:rfc', 'max:254'], 'queue_enabled' => ['required', 'boolean'],
        ];
    }
}
