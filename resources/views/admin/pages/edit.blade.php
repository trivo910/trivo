<x-admin.layout :title="$page->exists ? 'Page details' : 'Create page draft'">
    <div class="page-intro"><h2>{{ $page->exists ? $page->title : 'New page draft' }}</h2><p class="helper">Keep the original full path, including its trailing slash. Sections accept plain text and image-library references.</p></div>
    <div><x-admin.button :href="route('admin.pages.index')" variant="secondary">Back to pages</x-admin.button></div>
    @if($page->exists) @can('updateSeo', $page)<div><x-admin.button :href="route('admin.pages.seo', $page)" variant="secondary">Edit page SEO</x-admin.button></div>@endcan @endif
    @if(!$page->exists || auth()->user()->can('update', $page))
        <x-admin.glass-card strong>
            <form method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" class="form-stack">@csrf
                @if($page->exists) @method('PUT') <input type="hidden" name="content_version" value="{{ old('content_version', $page->content_version) }}">@endif
                <div class="form-grid">
                    <x-admin.input name="title" label="Page title" :value="old('title', $page->title)" maxlength="255" required />
                    <x-admin.input name="path" label="Full page path" :value="old('path', $page->path)" maxlength="768" placeholder="/existing-page-path" help="Use the exact original path. Imported page paths are locked." required />
                </div>
                <x-admin.page-sections :blocks="is_array(old('blocks', $page->blocks ?? [])) ? old('blocks', $page->blocks ?? []) : ($page->blocks ?? [])" :images="$images" />
                <p class="helper">{{ $page->status === 'published' ? 'Saving updates the live page immediately and keeps it published. The published URL is locked.' : 'Saving keeps this page as a draft. Manage SEO and publication separately below.' }}</p>
                <div><x-admin.button type="submit">{{ $page->status === 'published' ? 'Save published page' : 'Save page draft' }}</x-admin.button></div>
            </form>
        </x-admin.glass-card>
    @else
        <x-admin.glass-card strong><h2>Page information</h2><p>{{ $page->path ?? $page->archived_path }}</p><p class="helper">{{ $page->trashed() ? 'Archived draft' : ucfirst($page->status) }} · Version {{ $page->content_version }}. This page is read-only here.</p>
            @foreach($page->blocks ?? [] as $block)<p>{{ $block['text'] ?? $block['label'] ?? ('Image #'.($block['media_id'] ?? '')) }}</p>@endforeach
        </x-admin.glass-card>
    @endif
    @if($page->exists)
        <x-admin.publication-panel :model="$page" :history="$publicationHistory" route-prefix="admin.pages" label="page" />
        <x-admin.table caption="Revision history"><x-slot:head><tr><th scope="col">Version</th><th scope="col">Saved (UTC)</th><th scope="col">Action</th></tr></x-slot:head>
            @forelse($revisions as $revision)<tr><td>{{ $revision->snapshot['content_version'] ?? 'Imported' }}</td><td>{{ $revision->created_at->format('Y-m-d H:i:s') }}</td><td>
                @can('update', $page)<form method="POST" action="{{ route('admin.pages.revision', [$page, $revision]) }}">@csrf<input type="hidden" name="content_version" value="{{ $page->content_version }}"><x-admin.button type="submit" variant="secondary">Restore revision #{{ $revision->id }}</x-admin.button></form>
                @else Read only @endcan
            </td></tr>@empty<tr><td colspan="3">No revisions yet. Saving a draft creates a revision.</td></tr>@endforelse
        </x-admin.table>
        <p class="helper">Restoring creates a new version of the title and sections. The current path and SEO metadata are retained.</p>
        <div class="pagination">{{ $revisions->links() }}</div>
        @can('delete', $page)<x-admin.glass-card strong><h2>Archive draft</h2><p class="helper">Only unpublished drafts without imported URLs can be archived. Their paths and revision history remain reserved.</p>
            <form method="POST" action="{{ route('admin.pages.archive', $page) }}">@csrf @method('DELETE')<input type="hidden" name="content_version" value="{{ $page->content_version }}"><x-admin.button type="submit" variant="secondary">Archive page draft</x-admin.button></form>
        </x-admin.glass-card>@endcan
        @can('restore', $page)<x-admin.glass-card strong><h2>Restore draft</h2><form method="POST" action="{{ route('admin.pages.restore', $page) }}">@csrf<input type="hidden" name="content_version" value="{{ $page->content_version }}"><x-admin.button type="submit">Restore page draft</x-admin.button></form></x-admin.glass-card>@endcan
    @endif
</x-admin.layout>
