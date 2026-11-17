<x-admin.layout title="Portfolio">
    <div class="page-intro"><h2>Case studies</h2><p class="helper">Manage case-study sections, images, order, SEO and publication schedules. Entries with a case-study design render at their published URL.</p></div>
    @can('create', App\Models\PortfolioItem::class)<div><x-admin.button :href="route('admin.portfolio.create')">Create portfolio draft</x-admin.button></div>@endcan
    <x-admin.glass-card strong><form method="GET" action="{{ route('admin.portfolio.index') }}" class="form-grid">
        <x-admin.input name="q" label="Search portfolio" :value="request('q')" maxlength="120" />
        <x-admin.select name="state" label="Portfolio status" :value="request('state', 'active')" :options="['active' => 'Active entries', 'draft' => 'Drafts', 'published' => 'Published', 'scheduled' => 'Scheduled', 'archived' => 'Archived drafts']" />
        <div class="filter-actions"><x-admin.button type="submit" variant="secondary">Filter portfolio</x-admin.button></div>
    </form></x-admin.glass-card>
    <x-admin.table caption="Portfolio entries"><x-slot:head><tr><th scope="col">Entry</th><th scope="col">Path</th><th scope="col">Status</th><th scope="col">Order</th></tr></x-slot:head>
        @forelse($items as $item)<tr><td><a href="{{ route('admin.portfolio.edit', $item) }}">{{ $item->title }}</a></td><td class="path-cell">{{ $item->path }}</td><td>{{ $item->trashed() ? 'Archived' : ucfirst($item->status) }}</td><td>{{ $item->sort }}</td></tr>
        @empty<tr><td colspan="4">No entries found. Create a draft or adjust the filters.</td></tr>@endforelse
    </x-admin.table><div class="pagination">{{ $items->links() }}</div>
</x-admin.layout>
