<x-admin.layout title="Pages">
    <div class="page-intro"><h2>Website pages</h2><p class="helper">Manage drafts, SEO and publication schedules. Public templates and URL rendering are still in development.</p></div>
    @can('create', App\Models\Page::class)<div><x-admin.button :href="route('admin.pages.create')">Create page draft</x-admin.button></div>@endcan
    <x-admin.glass-card strong>
        <form method="GET" action="{{ route('admin.pages.index') }}" class="form-grid">
            <x-admin.input name="q" label="Search pages" :value="request('q')" maxlength="120" />
            <x-admin.select name="state" label="Page status" :value="request('state', 'active')" :options="['active' => 'Active pages', 'draft' => 'Drafts', 'published' => 'Published', 'scheduled' => 'Scheduled', 'archived' => 'Archived drafts']" />
            <div class="filter-actions"><x-admin.button type="submit" variant="secondary">Filter pages</x-admin.button></div>
        </form>
    </x-admin.glass-card>
    <x-admin.table caption="Pages"><x-slot:head><tr><th scope="col">Page</th><th scope="col">Path</th><th scope="col">Status</th><th scope="col">Version</th></tr></x-slot:head>
        @forelse($items as $item)<tr><td><a href="{{ route('admin.pages.edit', $item) }}">{{ $item->title }}</a></td><td class="path-cell">{{ $item->path }}</td><td>{{ $item->trashed() ? 'Archived' : ucfirst($item->status) }}</td><td>{{ $item->content_version }}</td></tr>
        @empty<tr><td colspan="4">No pages found. Create a draft or adjust the filters.</td></tr>@endforelse
    </x-admin.table>
    <div class="pagination">{{ $items->links() }}</div>
</x-admin.layout>
