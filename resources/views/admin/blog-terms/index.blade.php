<x-admin.layout :title="ucfirst($kind)">
    <div class="page-intro"><h2>Blog {{ $kind }}</h2><p class="helper">Manage blog terms and their exact archive paths. Public archive rendering and term SEO editing are still in development.</p></div>
    @can('create', $model::class)<div><x-admin.button :href="route('admin.blog.terms.create', $kind)">Create {{ $kind === 'categories' ? 'category' : 'tag' }}</x-admin.button></div>@endcan
    <x-admin.glass-card strong><form method="GET" action="{{ route('admin.blog.terms.index', $kind) }}" class="form-grid">
        <x-admin.input name="q" label="Search terms" :value="request('q')" maxlength="120" />
        <x-admin.select name="state" label="Term state" :value="request('state', 'active')" :options="['active' => 'Active terms', 'archived' => 'Archived terms']" />
        <div class="filter-actions"><x-admin.button type="submit" variant="secondary">Filter terms</x-admin.button></div>
    </form></x-admin.glass-card>
    <x-admin.table caption="Blog terms"><x-slot:head><tr><th scope="col">Name</th><th scope="col">Path</th><th scope="col">State</th></tr></x-slot:head>
        @forelse($items as $entry)<tr><td><a href="{{ route('admin.blog.terms.edit', ['kind' => $kind, 'term' => $entry->id]) }}">{{ $entry->name }}</a></td><td class="path-cell">{{ $entry->path }}</td><td>{{ $entry->trashed() ? 'Archived' : 'Active' }}</td></tr>
        @empty<tr><td colspan="3">No terms found. Create one or adjust the filters.</td></tr>@endforelse
    </x-admin.table><div class="pagination">{{ $items->links() }}</div>
</x-admin.layout>
