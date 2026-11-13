<x-admin.layout title="Leads">
    <div class="page-intro"><h2>Enquiry inbox</h2><p class="helper">Review enquiries, update their status and keep internal notes. Public forms and email replies are still in development.</p></div>
    <x-admin.glass-card strong><form method="GET" action="{{ route('admin.leads.index') }}" class="form-stack"><div class="form-grid">
        <x-admin.input name="q" label="Search leads" :value="request('q')" maxlength="120" help="Search name, email, phone or company." />
        <x-admin.select name="status" label="Lead status" :value="request('status', '')" :options="['' => 'All statuses'] + $statuses" />
        <x-admin.input name="service" label="Service (exact)" :value="request('service')" maxlength="150" />
        <x-admin.input name="from" label="Created from (UTC)" type="date" :value="request('from')" />
        <x-admin.input name="to" label="Created through (UTC)" type="date" :value="request('to')" />
        </div><div><x-admin.button type="submit" variant="secondary">Filter leads</x-admin.button></div>
    </form></x-admin.glass-card>
    @can('export', App\Models\Lead::class)<x-admin.glass-card strong><h2>Export enquiries</h2><p class="helper">CSV includes up to 1,000 matching enquiries, newest first. Internal notes, tracking data and IP hashes are excluded. Formula-like cells are exported as text.</p><x-admin.button :href="route('admin.leads.export', $filters)" variant="secondary">Export filtered CSV</x-admin.button></x-admin.glass-card>@endcan
    <x-admin.table caption="Enquiries"><x-slot:head><tr><th scope="col">Enquiry</th><th scope="col">Service</th><th scope="col">Status</th><th scope="col">Received (UTC)</th></tr></x-slot:head>
        @forelse($leads as $lead)<tr><td><a href="{{ route('admin.leads.show', $lead) }}">{{ $lead->name }}</a><br><span class="helper">{{ $lead->email }}</span></td><td>{{ $lead->service }}</td><td>{{ $statuses[$lead->status] ?? $lead->status }}</td><td>{{ $lead->created_at?->format('Y-m-d H:i:s') }}</td></tr>
        @empty<tr><td colspan="4">No enquiries found. Adjust the filters or return after enquiries are received.</td></tr>@endforelse
    </x-admin.table><div class="pagination">{{ $leads->links() }}</div>
</x-admin.layout>
