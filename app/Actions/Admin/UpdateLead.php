<?php

namespace App\Actions\Admin;

use App\Models\Lead;
use App\Services\Leads\LeadInbox;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateLead
{
    /** @param array<string, mixed> $values */
    public function handle(Lead $lead, array $values): void
    {
        Gate::authorize('update', $lead);
        DB::transaction(function () use ($lead, $values) {
            $lead = Lead::query()->lockForUpdate()->findOrFail($lead->id);
            Gate::authorize('update', $lead);
            $data = Validator::make($values, ['content_version' => ['required', 'integer', 'min:1'],
                'status' => ['required', Rule::in(array_keys(LeadInbox::STATUSES))], 'notes' => ['nullable', 'string', 'max:10000', function (string $attribute, mixed $value, Closure $fail): void {
                    if (is_string($value) && preg_match('/[<>\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
                        $fail('Use plain text without HTML or control characters.');
                    }
                }]])->validate();
            if ($lead->content_version !== (int) $data['content_version']) {
                throw ValidationException::withMessages(['content_version' => 'This lead changed. Reload before saving.']);
            }
            $lead->fill(['status' => $data['status'], 'notes' => isset($data['notes']) ? trim($data['notes']) : null, 'handled_by' => auth()->id()]);
            $lead->content_version++;
            $lead->save();
            activity('leads')->causedBy(auth()->user())->performedOn($lead)->event('lead_updated')->log('lead_updated');
        });
    }
}
