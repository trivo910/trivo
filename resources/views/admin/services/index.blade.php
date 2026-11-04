<x-admin.layout title="Services">
    <div class="page-intro"><h2>Website services</h2><p class="helper">Manage service sections, images, order, SEO and publication schedules. Public templates and URL rendering are still in development.</p></div>
    @can('create', App\Models\Service::class)<div><x-admin.button :href="route('admin.services.create')">Create service draft</x-admin.button></div>@endcan
    <x-admin.glass-card strong><form method="GET" action="{{ route('admin.services.index') }}" class="form-grid">
        <x-admin.input name="q" label="Search services" :value="request('q')" maxlength="120" />
        <x-admin.select name="state" label="Service status" :value="request('state', 'active')" :options="['active' => 'Active services', 'draft' => 'Drafts', 'published' => 'Published', 'scheduled' => 'Scheduled', 'archived' => 'Archived drafts']" />
        <div class="filter-actions"><x-admin.button type="submit" variant="secondary">Filter services</x-admin.button></div>
    </form></x-admin.glass-card>
    <x-admin.table caption="Services"><x-slot:head><tr><th scope="col">Service</th><th scope="col">Path</th><th scope="col">Status</th><th scope="col">Order</th></tr></x-slot:head>
        @forelse($items as $item)<tr><td><a href="{{ route('admin.services.edit', $item) }}">{{ $item->title }}</a></td><td class="path-cell">{{ $item->path }}</td><td>{{ $item->trashed() ? 'Archived' : ucfirst($item->status) }}</td><td>{{ $item->sort }}</td></tr>
        @empty<tr><td colspan="4">No services found. Create a draft or adjust the filters.</td></tr>@endforelse
    </x-admin.table><div class="pagination">{{ $items->links() }}</div>
</x-admin.layout>
