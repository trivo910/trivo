<x-admin.layout title="Testimonials">
    <div class="page-intro"><h2>Testimonials</h2><p class="helper">Manage testimonial drafts and display order. Public rendering, SEO and publication are still in development.</p></div>
    @can('create', App\Models\Testimonial::class)<div><x-admin.button :href="route('admin.testimonials.create')">Create testimonial draft</x-admin.button></div>@endcan
    <x-admin.glass-card strong><form method="GET" action="{{ route('admin.testimonials.index') }}" class="form-grid">
        <x-admin.input name="q" label="Search testimonials" :value="request('q')" maxlength="120" />
        <x-admin.select name="state" label="Testimonial status" :value="request('state', 'active')" :options="['active' => 'Active entries', 'draft' => 'Drafts', 'published' => 'Published', 'scheduled' => 'Scheduled', 'archived' => 'Archived drafts']" />
        <div class="filter-actions"><x-admin.button type="submit" variant="secondary">Filter testimonials</x-admin.button></div>
    </form></x-admin.glass-card>
    <x-admin.table caption="Testimonial drafts"><x-slot:head><tr><th scope="col">Entry</th><th scope="col">Path</th><th scope="col">Status</th><th scope="col">Order</th></tr></x-slot:head>
        @forelse($items as $item)<tr><td><a href="{{ route('admin.testimonials.edit', $item) }}">{{ $item->title }}</a></td><td class="path-cell">{{ $item->path }}</td><td>{{ $item->trashed() ? 'Archived' : ucfirst($item->status) }}</td><td>{{ $item->sort }}</td></tr>
        @empty<tr><td colspan="4">No entries found. Create a draft or adjust the filters.</td></tr>@endforelse
    </x-admin.table><div class="pagination">{{ $items->links() }}</div>
</x-admin.layout>
