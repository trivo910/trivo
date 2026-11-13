<x-admin.layout title="FAQs">
    <div class="page-intro"><h2>Frequently asked questions</h2><p class="helper">Manage FAQ drafts and display order. Public rendering, SEO and publication are still in development.</p></div>
    @can('create', App\Models\Faq::class)<div><x-admin.button :href="route('admin.faqs.create')">Create faq draft</x-admin.button></div>@endcan
    <x-admin.glass-card strong><form method="GET" action="{{ route('admin.faqs.index') }}" class="form-grid">
        <x-admin.input name="q" label="Search faqs" :value="request('q')" maxlength="120" />
        <x-admin.select name="state" label="FAQ status" :value="request('state', 'active')" :options="['active' => 'Active entries', 'draft' => 'Drafts', 'published' => 'Published', 'scheduled' => 'Scheduled', 'archived' => 'Archived drafts']" />
        <div class="filter-actions"><x-admin.button type="submit" variant="secondary">Filter faqs</x-admin.button></div>
    </form></x-admin.glass-card>
    <x-admin.table caption="FAQ drafts"><x-slot:head><tr><th scope="col">Entry</th><th scope="col">Path</th><th scope="col">Status</th><th scope="col">Order</th></tr></x-slot:head>
        @forelse($items as $item)<tr><td><a href="{{ route('admin.faqs.edit', $item) }}">{{ $item->title }}</a></td><td class="path-cell">{{ $item->path }}</td><td>{{ $item->trashed() ? 'Archived' : ucfirst($item->status) }}</td><td>{{ $item->sort }}</td></tr>
        @empty<tr><td colspan="4">No entries found. Create a draft or adjust the filters.</td></tr>@endforelse
    </x-admin.table><div class="pagination">{{ $items->links() }}</div>
</x-admin.layout>
