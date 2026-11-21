<x-admin.layout :title="$media->title ?: $media->original_name">
    <div class="component-row"><x-admin.button variant="secondary" :href="route('admin.media.index')">Back to library</x-admin.button><span class="badge">{{ $media->trashed() ? 'Archived' : ucfirst($media->processing_status) }}</span></div>
    <div class="styleguide-grid">
        <x-admin.glass-card strong>
            <h2>Image preview</h2>
            @if($media->processing_status === 'ready')<img class="media-detail-image" src="{{ route('admin.media.preview', $media) }}" alt="{{ $media->alt }}" width="{{ $media->width }}" height="{{ $media->height }}">
            @else<div x-data="mediaProcessing" data-initial-status="{{ $media->processing_status }}" data-status-url="{{ route('admin.media.status', $media) }}" class="media-placeholder"><p role="status">{{ $processingMessage }}</p></div>@endif
            <p class="helper">{{ $media->width }} × {{ $media->height }} · {{ number_format($media->size / 1024, 1) }} KiB</p>
            @if($media->processing_status === 'ready')<h3 class="media-subheading">Responsive formats</h3><ul class="plain-list">@foreach($variants as $variant)<li>{{ strtoupper(str_replace('image/', '', $variant['mime'])) }} · {{ $variant['width'] }} × {{ $variant['height'] }}</li>@endforeach</ul>@endif
            @can('retry', $media)<form method="POST" action="{{ route('admin.media.retry', $media) }}" class="form-stack">@csrf<x-admin.button type="submit" variant="secondary">Retry processing</x-admin.button></form>@endcan
        </x-admin.glass-card>
        <x-admin.glass-card strong>
            <h2>Image details</h2>
            @if(!$media->trashed())<form method="POST" action="{{ route('admin.media.update', $media) }}" class="form-stack">@csrf @method('PUT')
                <x-admin.input name="alt" label="Alt text" :value="old('alt', $media->alt)" required maxlength="1000" />
                <x-admin.input name="title" label="Image title" :value="old('title', $media->title)" maxlength="255" />
                <x-admin.textarea name="caption" label="Caption" :value="old('caption', $media->caption)" rows="3" maxlength="2000" />
                <x-admin.input name="folder" label="Folder" :value="old('folder', $media->folder)" maxlength="80" />
                <div><x-admin.button type="submit">Save image details</x-admin.button></div>
            </form>@else<p class="helper">Restore this image to edit its details.</p>@endif
        </x-admin.glass-card>
    </div>
    <x-admin.glass-card strong><h2>Image usage</h2>
        @if(collect($references)->contains('type', 'unreadable_settings'))<p class="helper" role="status">Some saved settings could not be read, so image usage cannot be fully checked. Archiving is blocked until those settings are restored or re-saved.</p>@endif
        @if($references)<ul class="plain-list">@foreach($references as $reference)<li>{{ ucfirst(str_replace('_', ' ', $reference['type'])) }} #{{ $reference['id'] }}</li>@endforeach</ul>
        @else<p class="helper">No references found in the current content or revisions.</p>@endif
        @if($media->isLegacy())<p class="helper">Legacy image files and their published URLs are protected from archiving.</p>@endif
    </x-admin.glass-card>
    @can('delete', $media)
        <x-admin.glass-card strong><h2>Archive image</h2><p class="helper">An unused image can be removed from the library. Its published files remain available so existing image URLs keep working.</p>
            <x-admin.modal title="Archive this image?" label="Archive image" id="archive-image"><form method="POST" action="{{ route('admin.media.archive', $media) }}">@csrf @method('DELETE')<x-admin.button type="submit" variant="danger">Confirm archive</x-admin.button></form></x-admin.modal>
        </x-admin.glass-card>
    @endcan
    @can('restore', $media)<x-admin.glass-card strong><h2>Restore image</h2><form method="POST" action="{{ route('admin.media.restore', $media) }}">@csrf<x-admin.button type="submit">Restore to library</x-admin.button></form></x-admin.glass-card>@endcan
</x-admin.layout>
