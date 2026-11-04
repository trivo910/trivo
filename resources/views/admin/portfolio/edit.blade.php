<x-admin.layout :title="$portfolio->exists ? 'Portfolio details' : 'Create portfolio draft'">
    <div class="page-intro"><h2>{{ $portfolio->exists ? $portfolio->title : 'New case-study draft' }}</h2><p class="helper">Preserve the original case-study path. Manage SEO and publication separately after saving; public templates are still in development.</p></div>
    <div><x-admin.button :href="route('admin.portfolio.index')" variant="secondary">Back to portfolio</x-admin.button></div>
    @if($portfolio->exists) @can('updateSeo', $portfolio)<div><x-admin.button :href="route('admin.portfolio.seo', $portfolio)" variant="secondary">Edit portfolio SEO</x-admin.button></div>@endcan @endif
    @if(!$portfolio->exists || auth()->user()->can('update', $portfolio))
        <x-admin.glass-card strong><form method="POST" action="{{ $portfolio->exists ? route('admin.portfolio.update', $portfolio) : route('admin.portfolio.store') }}" class="form-stack">@csrf
            @if($portfolio->exists) @method('PUT')<input type="hidden" name="content_version" value="{{ old('content_version', $portfolio->content_version) }}">@endif
            <div class="form-grid">
                <x-admin.input name="title" label="Portfolio title" :value="old('title', $portfolio->title)" maxlength="255" required />
                <x-admin.input name="path" label="Full portfolio path" :value="old('path', $portfolio->path)" maxlength="768" required help="Use the exact source path. Imported paths cannot be changed." />
                <x-admin.input name="sort" label="Display order" type="number" :value="old('sort', $portfolio->sort ?? 0)" min="0" max="10000" required help="Lower numbers appear first; ties use creation order." />
                <x-admin.select name="media_id" label="Featured portfolio image" :value="old('media_id', $portfolio->media_id ?? '')" :options="['' => 'No featured image'] + $images" help="Choose a ready image with alt text." />
            </div>
            <x-admin.page-sections title="Case-study sections" :blocks="is_array(old('blocks', $portfolio->blocks ?? [])) ? old('blocks', $portfolio->blocks ?? []) : ($portfolio->blocks ?? [])" :images="$images" />
            <div><x-admin.button type="submit">Save portfolio draft</x-admin.button></div>
        </form></x-admin.glass-card>
    @else
        <x-admin.glass-card strong><h2>Portfolio information</h2><p>{{ $portfolio->path }}</p><p class="helper">{{ $portfolio->trashed() ? 'Archived draft' : ucfirst($portfolio->status) }} · Version {{ $portfolio->content_version }} · Order {{ $portfolio->sort }}. This entry is read-only here.</p>
            @foreach($portfolio->blocks ?? [] as $block)<p>{{ $block['text'] ?? $block['label'] ?? ('Image #'.($block['media_id'] ?? '')) }}</p>@endforeach
        </x-admin.glass-card>
    @endif
    @if($portfolio->exists)
        <x-admin.publication-panel :model="$portfolio" :history="$publicationHistory" route-prefix="admin.portfolio" label="portfolio" />
        <x-admin.table caption="Portfolio revision history"><x-slot:head><tr><th scope="col">Version</th><th scope="col">Saved (UTC)</th><th scope="col">Action</th></tr></x-slot:head>
            @forelse($revisions as $revision)<tr><td>{{ $revision->snapshot['content_version'] }}</td><td>{{ $revision->created_at->format('Y-m-d H:i:s') }}</td><td>
                @can('update', $portfolio)<form method="POST" action="{{ route('admin.portfolio.revision', [$portfolio, $revision]) }}">@csrf<input type="hidden" name="content_version" value="{{ $portfolio->content_version }}"><x-admin.button type="submit" variant="secondary">Restore portfolio revision #{{ $revision->id }}</x-admin.button></form>@else Read only @endcan
            </td></tr>@empty<tr><td colspan="3">No revisions yet. Saving creates a revision.</td></tr>@endforelse
        </x-admin.table><p class="helper">Restoring creates a new version of the title, sections, image and display order. The current path and existing SEO/source fields are retained.</p>
        <div class="pagination">{{ $revisions->links() }}</div>
        @can('delete', $portfolio)<x-admin.glass-card strong><h2>Archive draft</h2><p class="helper">The path and revision history remain reserved after archiving.</p><form method="POST" action="{{ route('admin.portfolio.archive', $portfolio) }}">@csrf @method('DELETE')<input type="hidden" name="content_version" value="{{ $portfolio->content_version }}"><x-admin.button type="submit" variant="secondary">Archive portfolio draft</x-admin.button></form></x-admin.glass-card>@endcan
        @can('restore', $portfolio)<x-admin.glass-card strong><h2>Restore draft</h2><form method="POST" action="{{ route('admin.portfolio.restore', $portfolio) }}">@csrf<input type="hidden" name="content_version" value="{{ $portfolio->content_version }}"><x-admin.button type="submit">Restore portfolio draft</x-admin.button></form></x-admin.glass-card>@endcan
    @endif
</x-admin.layout>
