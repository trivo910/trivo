<?php

namespace App\Http\Requests\Admin;

use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PagePublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $page = $this->route('page');

        return $page instanceof Page && ($this->user()?->can($this->input('operation') === 'cancel' ? 'cancelPublication' : 'publish', $page) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['content_version' => ['required', 'integer', 'min:1'], 'operation' => ['required', Rule::in(['publish', 'schedule', 'cancel'])],
            'scheduled_for' => [Rule::excludeIf($this->input('operation') !== 'schedule'), 'required', 'date_format:Y-m-d\TH:i', 'after:now']];
    }
}
