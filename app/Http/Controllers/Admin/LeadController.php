<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ExportLeads;
use App\Actions\Admin\UpdateLead;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LeadIndexRequest;
use App\Http\Requests\Admin\LeadUpdateRequest;
use App\Models\Lead;
use App\Services\Leads\LeadInbox;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    public function index(LeadIndexRequest $request, LeadInbox $inbox): View
    {
        return view('admin.leads.index', ['leads' => $inbox->query($request->validated())->orderByDesc('id')->paginate(20)->withQueryString(),
            'statuses' => LeadInbox::STATUSES, 'filters' => array_filter($request->validated(), fn ($value) => $value !== null && $value !== '')]);
    }

    public function show(Lead $lead): View
    {
        Gate::authorize('view', $lead);

        return view('admin.leads.show', ['lead' => $lead, 'statuses' => LeadInbox::STATUSES]);
    }

    public function update(LeadUpdateRequest $request, Lead $lead, UpdateLead $action): RedirectResponse
    {
        $action->handle($lead, $request->validated());

        return redirect()->route('admin.leads.show', $lead)->with('status', 'Lead updated.');
    }

    public function export(LeadIndexRequest $request, ExportLeads $action): StreamedResponse
    {
        return $action->handle($request->validated());
    }
}
