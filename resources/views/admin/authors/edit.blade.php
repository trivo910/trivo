<x-admin.layout :title="$author->exists ? 'Author profile details' : 'Create author profile'">
    <div class="page-intro"><h2>{{ $author->exists ? $author->name : 'New author profile' }}</h2><p class="helper">This profile does not grant sign-in access. Preserve its exact archive path; saved paths stay fixed until a reviewed redirect workflow is available.</p></div>
    <div><x-admin.button :href="route('admin.authors.index')" variant="secondary">Back to authors</x-admin.button>@if($author->exists) @can('updateSeo', $author)<x-admin.button :href="route('admin.authors.seo', $author)" variant="secondary">SEO settings</x-admin.button>@endcan @endif</div>
    @if(!$author->exists || auth()->user()->can('update', $author))
        <x-admin.glass-card strong><form method="POST" action="{{ $author->exists ? route('admin.authors.update', $author) : route('admin.authors.store') }}" class="form-stack">@csrf
            @if($author->exists) @method('PUT')<input type="hidden" name="content_version" value="{{ old('content_version', $author->content_version) }}">@endif
            <div class="form-grid"><x-admin.input name="name" label="Author name" :value="old('name', $author->name)" maxlength="255" required />
                <x-admin.input name="path" label="Full author archive path" :value="old('path', $author->path)" maxlength="768" required help="Use the exact source path. Saved paths cannot be renamed here." />
                <x-admin.input name="avatar_id" label="Avatar image ID" type="number" min="1" :value="old('avatar_id', $author->avatar_id)" help="Optional. It must be a ready image with alt text in the image library." />
            </div><x-admin.textarea name="bio" label="Biography" :value="old('bio', $author->bio)" rows="6" maxlength="10000" help="Plain text only. Existing SEO fields remain unchanged." />
            <fieldset><legend>Social profile links</legend><p class="helper">Optional HTTPS URLs only. They are not fetched or published yet.</p><div class="form-grid">
                @foreach(['website' => 'Website', 'linkedin' => 'LinkedIn', 'x' => 'X', 'instagram' => 'Instagram'] as $key => $label)
                    <x-admin.input :name="'socials['.$key.']'" :label="$label" type="url" :value="old('socials.'.$key, data_get($author->socials, $key))" maxlength="2048" />
                @endforeach
            </div></fieldset><div><x-admin.button type="submit">Save author profile</x-admin.button></div>
        </form></x-admin.glass-card>
    @else
        <x-admin.glass-card strong><h2>Author information</h2><p>{{ $author->path }}</p><p>{{ $author->bio }}</p><p class="helper">{{ $author->trashed() ? 'Archived' : 'Read only' }} · Version {{ $author->content_version }}</p></x-admin.glass-card>
    @endif
    @if($author->exists)
        <x-admin.table caption="Author profile revision history"><x-slot:head><tr><th scope="col">Version</th><th scope="col">Saved (UTC)</th><th scope="col">Action</th></tr></x-slot:head>
            @forelse($revisions as $revision)<tr><td>{{ $revision->snapshot['content_version'] }}</td><td>{{ $revision->created_at->format('Y-m-d H:i:s') }}</td><td>@can('update', $author)<form method="POST" action="{{ route('admin.authors.revision', ['author' => $author, 'revision' => $revision]) }}">@csrf<input type="hidden" name="content_version" value="{{ $author->content_version }}"><x-admin.button type="submit" variant="secondary">Restore author revision #{{ $revision->id }}</x-admin.button></form>@else Read only @endcan</td></tr>@empty<tr><td colspan="3">No revisions yet.</td></tr>@endforelse
        </x-admin.table><div class="pagination">{{ $revisions->links() }}</div>
        @can('delete', $author)<x-admin.glass-card strong><h2>Archive author profile</h2><p class="helper">Imported profiles and profiles assigned to posts cannot be archived. Paths and history stay reserved.</p><form method="POST" action="{{ route('admin.authors.archive', $author) }}">@csrf @method('DELETE')<input type="hidden" name="content_version" value="{{ $author->content_version }}"><x-admin.button type="submit" variant="secondary">Archive author profile</x-admin.button></form></x-admin.glass-card>@endcan
        @can('restore', $author)<x-admin.glass-card strong><h2>Restore author profile</h2><form method="POST" action="{{ route('admin.authors.restore', $author) }}">@csrf<input type="hidden" name="content_version" value="{{ $author->content_version }}"><x-admin.button type="submit">Restore author profile</x-admin.button></form></x-admin.glass-card>@endcan
    @endif
</x-admin.layout>
