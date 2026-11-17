<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'form' => ['required', Rule::in(['contact', 'audit', 'proposal'])],
            'name' => ['required', 'string', 'max:150', 'not_regex:/[\r\n]/'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'phone' => ['required', 'string', 'max:50', 'regex:/^[0-9+().\s-]+$/D'],
            'company' => ['nullable', 'string', 'max:255', 'not_regex:/[\r\n]/'],
            'message' => ['required_if:form,contact', 'nullable', 'string', 'max:10000'],
            'website' => ['nullable', 'string', 'max:0'],
        ];
    }
}
