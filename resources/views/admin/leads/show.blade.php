<x-admin.layout title="Lead details">
    <div class="page-intro"><h2>{{ $lead->name }}</h2><p class="helper">Review the original enquiry and record internal follow-up notes.</p></div>
    <div><x-admin.button :href="route('admin.leads.index')" variant="secondary">Back to leads</x-admin.button></div>
    <x-admin.glass-card strong><h2>Enquiry details</h2><dl>
        <dt>Email</dt><dd>{{ $lead->email }}</dd><dt>Phone</dt><dd>{{ $lead->phone ?: 'Not provided' }}</dd>
        <dt>Company</dt><dd>{{ $lead->company ?: 'Not provided' }}</dd><dt>Service</dt><dd>{{ $lead->service ?: 'Not provided' }}</dd>
        <dt>Source URL</dt><dd class="path-cell">{{ $lead->source_url ?: 'Not provided' }}</dd><dt>Received (UTC)</dt><dd>{{ $lead->created_at?->format('Y-m-d H:i:s') }}</dd>
        <dt>Status</dt><dd>{{ $statuses[$lead->status] ?? $lead->status }}</dd><dt>Last handled by</dt><dd>{{ $lead->handled_by ? 'Account #'.$lead->handled_by : 'Not yet handled' }}</dd>
    </dl><h3>Message</h3>@foreach(preg_split('/\r\n|\r|\n/', $lead->message ?? '') ?: [] as $line)<p>{{ $line }}</p>@endforeach</x-admin.glass-card>
    <x-admin.table caption="Enquiry email delivery">
        <x-slot:head><tr><th scope="col">Email</th><th scope="col">Status</th><th scope="col">Result</th></tr></x-slot:head>
        @forelse($mailLogs as $log)
            <tr><td>{{ $log->purpose === 'lead_thank_you' ? 'Visitor thank-you' : 'Admin notification' }}</td><td>{{ ucfirst($log->status) }}</td><td>{{ $log->error_message ?: ($log->sent_at ? 'SMTP accepted at '.$log->sent_at->format('Y-m-d H:i:s').' UTC' : 'Awaiting background processing') }}</td></tr>
        @empty<tr><td colspan="3">No email delivery records for this enquiry.</td></tr>@endforelse
    </x-admin.table>
    @can('update', $lead)
        <x-admin.glass-card strong><h2>Follow-up</h2><form method="POST" action="{{ route('admin.leads.update', $lead) }}" class="form-stack">@csrf @method('PUT')
            <input type="hidden" name="content_version" value="{{ old('content_version', $lead->content_version) }}">
            <x-admin.select name="status" label="Update status" :value="old('status', $lead->status)" :options="$statuses" required />
            <x-admin.textarea name="notes" label="Internal notes" :value="old('notes', $lead->notes)" rows="7" maxlength="10000" help="Plain text only. Notes are not emailed or included in CSV exports." />
            <div><x-admin.button type="submit">Save lead</x-admin.button></div>
        </form></x-admin.glass-card>
    @else
        <x-admin.glass-card strong><h2>Internal notes</h2>@foreach(preg_split('/\r\n|\r|\n/', $lead->notes ?? '') ?: [] as $line)<p>{{ $line }}</p>@endforeach<p class="helper">This enquiry is read-only for your account.</p></x-admin.glass-card>
    @endcan
</x-admin.layout>
