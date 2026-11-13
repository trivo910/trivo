<x-admin.layout :title="$faq->exists ? 'FAQ details' : 'Create faq draft'">
    <div class="page-intro"><h2>{{ $faq->exists ? $faq->title : 'New FAQ draft' }}</h2><p class="helper">Write the question and answer sections, then set their display order. Public rendering is still in development.</p></div>
    <div><x-admin.button :href="route('admin.faqs.index')" variant="secondary">Back to faq</x-admin.button>@if($faq->exists) @can('updateSeo', $faq)<x-admin.button :href="route('admin.faqs.seo', $faq)" variant="secondary">SEO settings</x-admin.button>@endcan @endif</div>
    @if(!$faq->exists || auth()->user()->can('update', $faq))
        <x-admin.glass-card strong><form method="POST" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}" class="form-stack">@csrf
            @if($faq->exists) @method('PUT')<input type="hidden" name="content_version" value="{{ old('content_version', $faq->content_version) }}">@endif
            <div class="form-grid">
                <x-admin.input name="title" label="FAQ question" :value="old('title', $faq->title)" maxlength="255" required />
                <x-admin.input name="path" label="Full faq path (optional)" :value="old('path', $faq->path)" maxlength="768" help="Leave blank for an embedded FAQ. Preserve exact imported paths when present." />
                <x-admin.input name="sort" label="Display order" type="number" :value="old('sort', $faq->sort ?? 0)" min="0" max="10000" required help="Lower numbers appear first; ties use creation order." />
                <x-admin.select name="media_id" label="FAQ image" :value="old('media_id', $faq->media_id ?? '')" :options="['' => 'No featured image'] + $images" help="Choose a ready image with alt text." />
            </div>
            <x-admin.page-sections title="Answer sections" :blocks="is_array(old('blocks', $faq->blocks ?? [])) ? old('blocks', $faq->blocks ?? []) : ($faq->blocks ?? [])" :images="$images" />
            <div><x-admin.button type="submit">Save faq draft</x-admin.button></div>
        </form></x-admin.glass-card>
    @else
        <x-admin.glass-card strong><h2>FAQ information</h2><p>{{ $faq->path }}</p><p class="helper">{{ $faq->trashed() ? 'Archived draft' : ucfirst($faq->status) }} · Version {{ $faq->content_version }} · Order {{ $faq->sort }}. This entry is read-only here.</p>
            @foreach($faq->blocks ?? [] as $block)<p>{{ $block['text'] ?? $block['label'] ?? ('Image #'.($block['media_id'] ?? '')) }}</p>@endforeach
        </x-admin.glass-card>
    @endif
    @if($faq->exists)
        <x-admin.table caption="FAQ revision history"><x-slot:head><tr><th scope="col">Version</th><th scope="col">Saved (UTC)</th><th scope="col">Action</th></tr></x-slot:head>
            @forelse($revisions as $revision)<tr><td>{{ $revision->snapshot['content_version'] }}</td><td>{{ $revision->created_at->format('Y-m-d H:i:s') }}</td><td>
                @can('update', $faq)<form method="POST" action="{{ route('admin.faqs.revision', [$faq, $revision]) }}">@csrf<input type="hidden" name="content_version" value="{{ $faq->content_version }}"><x-admin.button type="submit" variant="secondary">Restore faq revision #{{ $revision->id }}</x-admin.button></form>@else Read only @endcan
            </td></tr>@empty<tr><td colspan="3">No revisions yet. Saving creates a revision.</td></tr>@endforelse
        </x-admin.table><p class="helper">Restoring creates a new version of the title, sections, image and display order. The current path and existing source fields are retained.</p>
        <div class="pagination">{{ $revisions->links() }}</div>
        @can('delete', $faq)<x-admin.glass-card strong><h2>Archive draft</h2><p class="helper">The path and revision history remain reserved after archiving.</p><form method="POST" action="{{ route('admin.faqs.archive', $faq) }}">@csrf @method('DELETE')<input type="hidden" name="content_version" value="{{ $faq->content_version }}"><x-admin.button type="submit" variant="secondary">Archive faq draft</x-admin.button></form></x-admin.glass-card>@endcan
        @can('restore', $faq)<x-admin.glass-card strong><h2>Restore draft</h2><form method="POST" action="{{ route('admin.faqs.restore', $faq) }}">@csrf<input type="hidden" name="content_version" value="{{ $faq->content_version }}"><x-admin.button type="submit">Restore faq draft</x-admin.button></form></x-admin.glass-card>@endcan
    @endif
</x-admin.layout>
