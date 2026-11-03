<?php

namespace App\Http\Requests\Admin;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class TestMailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('testMail', Setting::class) ?? false;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['recipient' => ['required', 'email:rfc', 'max:254']];
    }
}
