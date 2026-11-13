<?php

namespace App\Http\Requests\Admin;

use App\Models\Lead;
use App\Services\Leads\LeadInbox;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $lead = $this->route('lead');

        return $lead instanceof Lead && ($this->user()?->can('update', $lead) ?? false);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['content_version' => ['required', 'integer', 'min:1'], 'status' => ['required', Rule::in(array_keys(LeadInbox::STATUSES))],
            'notes' => ['nullable', 'string', 'max:10000']];
    }
}
