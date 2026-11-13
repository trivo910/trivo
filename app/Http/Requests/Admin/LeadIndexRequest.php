<?php

namespace App\Http\Requests\Admin;

use App\Models\Lead;
use App\Services\Leads\LeadInbox;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can($this->routeIs('admin.leads.export') ? 'export' : 'viewAny', Lead::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['q' => ['nullable', 'string', 'max:120'], 'status' => ['nullable', Rule::in(array_keys(LeadInbox::STATUSES))],
            'service' => ['nullable', 'string', 'max:150'], 'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', ...($this->filled('from') ? ['after_or_equal:from'] : [])]];
    }
}
