<x-admin.layout :title="$term->exists ? 'Blog term details' : 'Create blog term'">
    <div class="page-intro"><h2>{{ $term->exists ? $term->name : ($kind === 'categories' ? 'New category' : 'New tag') }}</h2><p class="helper">Preserve the exact archive path. Saved paths stay fixed until a reviewed redirect workflow is available.</p></div>
    <div><x-admin.button :href="route('admin.blog.terms.index', $kind)" variant="secondary">Back to {{ $kind }}</x-admin.button></div>
    @if(!$term->exists || auth()->user()->can('update', $term))
        <x-admin.glass-card strong><form method="POST" action="{{ $term->exists ? route('admin.blog.terms.update', ['kind' => $kind, 'term' => $term->id]) : route('admin.blog.terms.store', $kind) }}" class="form-stack">@csrf
            @if($term->exists) @method('PUT')<input type="hidden" name="content_version" value="{{ old('content_version', $term->content_version) }}">@endif
            <div class="form-grid"><x-admin.input name="name" label="Term name" :value="old('name', $term->name)" maxlength="255" required />
                <x-admin.input name="slug" label="Term slug" :value="old('slug', $term->slug)" maxlength="255" required help="Stored separately; it does not regenerate the exact path." />
                <x-admin.input name="path" label="Full archive path" :value="old('path', $term->path)" maxlength="768" required help="Use the exact source path. Saved paths cannot be renamed here." />
                @if($kind === 'categories')<x-admin.select name="parent_id" label="Parent category" :value="old('parent_id', $term->parent_id ?? '')" :options="$parents" help="Up to five levels. Cycles and inactive parents are rejected." />@endif
            </div><x-admin.textarea name="description" label="Term description" :value="old('description', $term->description)" rows="5" maxlength="10000" help="Plain text only. Existing SEO fields remain unchanged." />
            <div><x-admin.button type="submit">Save blog term</x-admin.button></div>
        </form></x-admin.glass-card>
    @else
        <x-admin.glass-card strong><h2>Term information</h2><p>{{ $term->path }}</p><p>{{ $term->slug }}</p><p>{{ $term->description }}</p><p class="helper">{{ $term->trashed() ? 'Archived' : 'Read only' }} ? Version {{ $term->content_version }}</p></x-admin.glass-card>
    @endif
    @if($term->exists)
        <x-admin.table caption="Term revision history"><x-slot:head><tr><th scope="col">Version</th><th scope="col">Saved (UTC)</th><th scope="col">Action</th></tr></x-slot:head>
            @forelse($revisions as $revision)<tr><td>{{ $revision->snapshot['content_version'] }}</td><td>{{ $revision->created_at->format('Y-m-d H:i:s') }}</td><td>@can('update', $term)<form method="POST" action="{{ route('admin.blog.terms.revision', ['kind' => $kind, 'term' => $term->id, 'revision' => $revision]) }}">@csrf<input type="hidden" name="content_version" value="{{ $term->content_version }}"><x-admin.button type="submit" variant="secondary">Restore term revision #{{ $revision->id }}</x-admin.button></form>@else Read only @endcan</td></tr>@empty<tr><td colspan="3">No revisions yet.</td></tr>@endforelse
        </x-admin.table><div class="pagination">{{ $revisions->links() }}</div>
        @can('delete', $term)<x-admin.glass-card strong><h2>Archive term</h2><p class="helper">Imported terms and terms assigned to posts cannot be archived. Paths and history stay reserved.</p><form method="POST" action="{{ route('admin.blog.terms.archive', ['kind' => $kind, 'term' => $term->id]) }}">@csrf @method('DELETE')<input type="hidden" name="content_version" value="{{ $term->content_version }}"><x-admin.button type="submit" variant="secondary">Archive blog term</x-admin.button></form></x-admin.glass-card>@endcan
        @can('restore', $term)<x-admin.glass-card strong><h2>Restore term</h2><form method="POST" action="{{ route('admin.blog.terms.restore', ['kind' => $kind, 'term' => $term->id]) }}">@csrf<input type="hidden" name="content_version" value="{{ $term->content_version }}"><x-admin.button type="submit">Restore blog term</x-admin.button></form></x-admin.glass-card>@endcan
    @endif
</x-admin.layout>
