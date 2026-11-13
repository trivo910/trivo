<x-admin.layout :title="$testimonial->exists ? 'Testimonial details' : 'Create testimonial draft'">
    <div class="page-intro"><h2>{{ $testimonial->exists ? $testimonial->title : 'New testimonial draft' }}</h2><p class="helper">Write the testimonial quote sections, then set their display order. Public rendering is still in development.</p></div>
    <div><x-admin.button :href="route('admin.testimonials.index')" variant="secondary">Back to testimonials</x-admin.button></div>
    @if(!$testimonial->exists || auth()->user()->can('update', $testimonial))
        <x-admin.glass-card strong><form method="POST" action="{{ $testimonial->exists ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}" class="form-stack">@csrf
            @if($testimonial->exists) @method('PUT')<input type="hidden" name="content_version" value="{{ old('content_version', $testimonial->content_version) }}">@endif
            <div class="form-grid">
                <x-admin.input name="title" label="Testimonial title" :value="old('title', $testimonial->title)" maxlength="255" required />
                <x-admin.input name="path" label="Full testimonial path (optional)" :value="old('path', $testimonial->path)" maxlength="768" help="Leave blank for an embedded testimonial. Preserve exact imported paths when present." />
                <x-admin.input name="sort" label="Display order" type="number" :value="old('sort', $testimonial->sort ?? 0)" min="0" max="10000" required help="Lower numbers appear first; ties use creation order." />
                <x-admin.select name="media_id" label="Testimonial image" :value="old('media_id', $testimonial->media_id ?? '')" :options="['' => 'No featured image'] + $images" help="Choose a ready image with alt text." />
            </div>
            <x-admin.page-sections title="Quote sections" :blocks="is_array(old('blocks', $testimonial->blocks ?? [])) ? old('blocks', $testimonial->blocks ?? []) : ($testimonial->blocks ?? [])" :images="$images" />
            <div><x-admin.button type="submit">Save testimonial draft</x-admin.button></div>
        </form></x-admin.glass-card>
    @else
        <x-admin.glass-card strong><h2>Testimonial information</h2><p>{{ $testimonial->path }}</p><p class="helper">{{ $testimonial->trashed() ? 'Archived draft' : ucfirst($testimonial->status) }} · Version {{ $testimonial->content_version }} · Order {{ $testimonial->sort }}. This entry is read-only here.</p>
            @foreach($testimonial->blocks ?? [] as $block)<p>{{ $block['text'] ?? $block['label'] ?? ('Image #'.($block['media_id'] ?? '')) }}</p>@endforeach
        </x-admin.glass-card>
    @endif
    @if($testimonial->exists)
        <x-admin.table caption="Testimonial revision history"><x-slot:head><tr><th scope="col">Version</th><th scope="col">Saved (UTC)</th><th scope="col">Action</th></tr></x-slot:head>
            @forelse($revisions as $revision)<tr><td>{{ $revision->snapshot['content_version'] }}</td><td>{{ $revision->created_at->format('Y-m-d H:i:s') }}</td><td>
                @can('update', $testimonial)<form method="POST" action="{{ route('admin.testimonials.revision', [$testimonial, $revision]) }}">@csrf<input type="hidden" name="content_version" value="{{ $testimonial->content_version }}"><x-admin.button type="submit" variant="secondary">Restore testimonial revision #{{ $revision->id }}</x-admin.button></form>@else Read only @endcan
            </td></tr>@empty<tr><td colspan="3">No revisions yet. Saving creates a revision.</td></tr>@endforelse
        </x-admin.table><p class="helper">Restoring creates a new version of the title, sections, image and display order. The current path and existing source fields are retained.</p>
        <div class="pagination">{{ $revisions->links() }}</div>
        @can('delete', $testimonial)<x-admin.glass-card strong><h2>Archive draft</h2><p class="helper">The path and revision history remain reserved after archiving.</p><form method="POST" action="{{ route('admin.testimonials.archive', $testimonial) }}">@csrf @method('DELETE')<input type="hidden" name="content_version" value="{{ $testimonial->content_version }}"><x-admin.button type="submit" variant="secondary">Archive testimonial draft</x-admin.button></form></x-admin.glass-card>@endcan
        @can('restore', $testimonial)<x-admin.glass-card strong><h2>Restore draft</h2><form method="POST" action="{{ route('admin.testimonials.restore', $testimonial) }}">@csrf<input type="hidden" name="content_version" value="{{ $testimonial->content_version }}"><x-admin.button type="submit">Restore testimonial draft</x-admin.button></form></x-admin.glass-card>@endcan
    @endif
</x-admin.layout>
