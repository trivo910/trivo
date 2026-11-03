<x-admin.layout title="Image library">
    <div class="page-intro"><h2>Website images</h2><p class="helper">Upload images, edit descriptions and reuse existing assets.</p></div>
    <div><x-admin.button :href="route('admin.media.create')">Upload image</x-admin.button></div>
    <x-admin.glass-card strong>
        <form method="GET" action="{{ route('admin.media.index') }}" class="form-grid">
            <x-admin.input name="q" label="Search images" :value="request('q')" maxlength="120" />
            <x-admin.select name="state" label="Show" :value="request('state', 'active')" :options="['active' => 'Active images', 'archived' => 'Archived images', 'queued' => 'Queued', 'processing' => 'Processing', 'failed' => 'Needs attention']" />
            <x-admin.select name="folder" label="Folder" :value="request('folder', '')" :options="['' => 'All folders'] + $folders->mapWithKeys(fn($folder) => [$folder => $folder])->all()" />
            <div class="filter-actions"><x-admin.button type="submit" variant="secondary">Filter images</x-admin.button></div>
        </form>
    </x-admin.glass-card>
    <div class="media-grid">
        @forelse($items as $item)
            <x-admin.glass-card strong class="media-tile">
                <a href="{{ route('admin.media.show', $item) }}" class="media-card-link">
                    @if($item->processing_status === 'ready')<img class="media-thumbnail" src="{{ route('admin.media.preview', $item) }}" alt="" width="480" height="320" loading="lazy">
                    @else<div class="media-placeholder">{{ ucfirst($item->processing_status) }}</div>@endif
                    <span class="media-name">{{ $item->title ?: $item->original_name }}</span>
                </a>
                <p class="helper">{{ $item->width }} × {{ $item->height }} · {{ ucfirst($item->processing_status) }}{{ $item->trashed() ? ' · Archived' : '' }}</p>
            </x-admin.glass-card>
        @empty<x-admin.glass-card strong><h2>No images found</h2><p class="helper">Upload an image or adjust the filters.</p></x-admin.glass-card>@endforelse
    </div>
    <div class="pagination">{{ $items->links() }}</div>
</x-admin.layout>
