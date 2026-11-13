<x-admin.layout title="Blog authors">
    <div class="page-intro"><h2>Blog authors</h2><p class="helper">Author profiles are content records, separate from administration accounts. Public author archives and author SEO editing are still in development.</p></div>
    @can('create', \App\Models\Author::class)<div><x-admin.button :href="route('admin.authors.create')">Create author profile</x-admin.button></div>@endcan
    <x-admin.glass-card strong><form method="GET" action="{{ route('admin.authors.index') }}" class="form-grid">
        <x-admin.input name="q" label="Search authors" :value="request('q')" maxlength="120" />
        <x-admin.select name="state" label="Author state" :value="request('state', 'active')" :options="['active' => 'Active authors', 'archived' => 'Archived authors']" />
        <div class="filter-actions"><x-admin.button type="submit" variant="secondary">Filter authors</x-admin.button></div>
    </form></x-admin.glass-card>
    <x-admin.table caption="Blog author profiles"><x-slot:head><tr><th scope="col">Name</th><th scope="col">Path</th><th scope="col">State</th></tr></x-slot:head>
        @forelse($authors as $author)<tr><td><a href="{{ route('admin.authors.edit', $author) }}">{{ $author->name }}</a></td><td class="path-cell">{{ $author->path }}</td><td>{{ $author->trashed() ? 'Archived' : 'Active' }}</td></tr>
        @empty<tr><td colspan="3">No author profiles found. Create one or adjust the filters.</td></tr>@endforelse
    </x-admin.table><div class="pagination">{{ $authors->links() }}</div>
</x-admin.layout>
