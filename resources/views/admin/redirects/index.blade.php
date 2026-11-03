<x-admin.layout title="Redirects">
    <div class="page-intro"><h2>URL redirects</h2><p class="helper">Preserve old URLs with a single destination. Literal paths are supported.</p></div>
    <div class="component-row"><x-admin.button :href="route('admin.redirects.create')">Add redirect</x-admin.button><x-admin.button variant="secondary" :href="route('admin.not-found')">View 404 log</x-admin.button></div>
    <x-admin.table caption="Saved redirects"><x-slot:head><tr><th scope="col">Source</th><th scope="col">Destination</th><th scope="col">Status</th><th scope="col">Hits</th><th scope="col">Actions</th></tr></x-slot:head>
        @forelse($redirects as $redirect)<tr><td class="path-cell">{{ $redirect->from_path }}</td><td class="path-cell">{{ $redirect->to_url ?? 'Removed' }}</td><td>{{ $redirect->status_code }}</td><td>{{ $redirect->hits }}</td><td>@can('update', $redirect)<x-admin.button variant="secondary" :href="route('admin.redirects.edit', $redirect)">Edit</x-admin.button>@endcan</td></tr>
        @empty<tr><td colspan="5">No redirects saved yet.</td></tr>@endforelse
    </x-admin.table>
    <div class="pagination">{{ $redirects->links() }}</div>
    <x-admin.glass-card strong><h2>Import redirects</h2><p class="helper">CSV headers: from_path,to_url,status_code. Up to 1,000 rows. Existing source paths are updated; invalid files are rejected as a whole.</p>
        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.redirects.import') }}" class="form-stack">@csrf
            <x-admin.input name="csv" label="Redirect CSV" type="file" accept=".csv,text/csv" required />
            <div><x-admin.button type="submit" variant="secondary">Import CSV</x-admin.button></div>
        </form>
    </x-admin.glass-card>
</x-admin.layout>
