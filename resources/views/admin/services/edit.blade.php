<x-admin.layout :title="$service->exists ? 'Service details' : 'Create service draft'">
    <div class="page-intro"><h2>{{ $service->exists ? $service->title : 'New service draft' }}</h2><p class="helper">Preserve the original service path. Manage SEO and publication separately after saving; public templates are still in development.</p></div>
    <div><x-admin.button :href="route('admin.services.index')" variant="secondary">Back to services</x-admin.button></div>
    @if($service->exists) @can('updateSeo', $service)<div><x-admin.button :href="route('admin.services.seo', $service)" variant="secondary">Edit service SEO</x-admin.button></div>@endcan @endif
    @if(!$service->exists || auth()->user()->can('update', $service))
        <x-admin.glass-card strong><form method="POST" action="{{ $service->exists ? route('admin.services.update', $service) : route('admin.services.store') }}" class="form-stack">@csrf
            @if($service->exists) @method('PUT')<input type="hidden" name="content_version" value="{{ old('content_version', $service->content_version) }}">@endif
            <div class="form-grid">
                <x-admin.input name="title" label="Service title" :value="old('title', $service->title)" maxlength="255" required />
                <x-admin.input name="path" label="Full service path" :value="old('path', $service->path)" maxlength="768" required help="Use the exact source path. Imported paths cannot be changed." />
                <x-admin.input name="sort" label="Display order" type="number" :value="old('sort', $service->sort ?? 0)" min="0" max="10000" required help="Lower numbers appear first; ties use creation order." />
                <x-admin.select name="media_id" label="Featured service image" :value="old('media_id', $service->media_id ?? '')" :options="['' => 'No featured image'] + $images" help="Choose a ready image with alt text." />
            </div>
            <x-admin.page-sections title="Service sections" :blocks="is_array(old('blocks', $service->blocks ?? [])) ? old('blocks', $service->blocks ?? []) : ($service->blocks ?? [])" :images="$images" />
            <div><x-admin.button type="submit">Save service draft</x-admin.button></div>
        </form></x-admin.glass-card>
    @else
        <x-admin.glass-card strong><h2>Service information</h2><p>{{ $service->path }}</p><p class="helper">{{ $service->trashed() ? 'Archived draft' : ucfirst($service->status) }} · Version {{ $service->content_version }} · Order {{ $service->sort }}. This service is read-only here.</p>
            @foreach($service->blocks ?? [] as $block)<p>{{ $block['text'] ?? $block['label'] ?? ('Image #'.($block['media_id'] ?? '')) }}</p>@endforeach
        </x-admin.glass-card>
    @endif
    @if($service->exists)
        <x-admin.publication-panel :model="$service" :history="$publicationHistory" route-prefix="admin.services" label="service" />
        <x-admin.table caption="Service revision history"><x-slot:head><tr><th scope="col">Version</th><th scope="col">Saved (UTC)</th><th scope="col">Action</th></tr></x-slot:head>
            @forelse($revisions as $revision)<tr><td>{{ $revision->snapshot['content_version'] }}</td><td>{{ $revision->created_at->format('Y-m-d H:i:s') }}</td><td>
                @can('update', $service)<form method="POST" action="{{ route('admin.services.revision', [$service, $revision]) }}">@csrf<input type="hidden" name="content_version" value="{{ $service->content_version }}"><x-admin.button type="submit" variant="secondary">Restore service revision #{{ $revision->id }}</x-admin.button></form>@else Read only @endcan
            </td></tr>@empty<tr><td colspan="3">No revisions yet. Saving creates a revision.</td></tr>@endforelse
        </x-admin.table><p class="helper">Restoring creates a new version of the title, sections, image and display order. The current path and existing SEO/source fields are retained.</p>
        <div class="pagination">{{ $revisions->links() }}</div>
        @can('delete', $service)<x-admin.glass-card strong><h2>Archive draft</h2><p class="helper">The path and revision history remain reserved after archiving.</p><form method="POST" action="{{ route('admin.services.archive', $service) }}">@csrf @method('DELETE')<input type="hidden" name="content_version" value="{{ $service->content_version }}"><x-admin.button type="submit" variant="secondary">Archive service draft</x-admin.button></form></x-admin.glass-card>@endcan
        @can('restore', $service)<x-admin.glass-card strong><h2>Restore draft</h2><form method="POST" action="{{ route('admin.services.restore', $service) }}">@csrf<input type="hidden" name="content_version" value="{{ $service->content_version }}"><x-admin.button type="submit">Restore service draft</x-admin.button></form></x-admin.glass-card>@endcan
    @endif
</x-admin.layout>
