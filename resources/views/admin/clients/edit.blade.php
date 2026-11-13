<x-admin.layout :title="$client->exists ? 'Client details' : 'Create client draft'">
    <div class="page-intro"><h2>{{ $client->exists ? $client->title : 'New client draft' }}</h2><p class="helper">Manage the client name, logo and display order. Add supporting sections when needed. Public rendering is still in development.</p></div>
    <div><x-admin.button :href="route('admin.clients.index')" variant="secondary">Back to clients</x-admin.button></div>
    @if(!$client->exists || auth()->user()->can('update', $client))
        <x-admin.glass-card strong><form method="POST" action="{{ $client->exists ? route('admin.clients.update', $client) : route('admin.clients.store') }}" class="form-stack">@csrf
            @if($client->exists) @method('PUT')<input type="hidden" name="content_version" value="{{ old('content_version', $client->content_version) }}">@endif
            <div class="form-grid">
                <x-admin.input name="title" label="Client name" :value="old('title', $client->title)" maxlength="255" required />
                <x-admin.input name="path" label="Full client path (optional)" :value="old('path', $client->path)" maxlength="768" help="Leave blank for an embedded client. Preserve exact imported paths when present." />
                <x-admin.input name="sort" label="Display order" type="number" :value="old('sort', $client->sort ?? 0)" min="0" max="10000" required help="Lower numbers appear first; ties use creation order." />
                <x-admin.select name="media_id" label="Client logo" :value="old('media_id', $client->media_id ?? '')" :options="['' => 'No logo'] + $images" help="Choose a ready image with alt text." />
            </div>
            <x-admin.page-sections title="Client sections" :blocks="is_array(old('blocks', $client->blocks ?? [])) ? old('blocks', $client->blocks ?? []) : ($client->blocks ?? [])" :images="$images" />
            <div><x-admin.button type="submit">Save client draft</x-admin.button></div>
        </form></x-admin.glass-card>
    @else
        <x-admin.glass-card strong><h2>Client information</h2><p>{{ $client->path }}</p><p class="helper">{{ $client->trashed() ? 'Archived draft' : ucfirst($client->status) }} · Version {{ $client->content_version }} · Order {{ $client->sort }}. This entry is read-only here.</p>
            @foreach($client->blocks ?? [] as $block)<p>{{ $block['text'] ?? $block['label'] ?? ('Image #'.($block['media_id'] ?? '')) }}</p>@endforeach
        </x-admin.glass-card>
    @endif
    @if($client->exists)
        <x-admin.table caption="Client revision history"><x-slot:head><tr><th scope="col">Version</th><th scope="col">Saved (UTC)</th><th scope="col">Action</th></tr></x-slot:head>
            @forelse($revisions as $revision)<tr><td>{{ $revision->snapshot['content_version'] }}</td><td>{{ $revision->created_at->format('Y-m-d H:i:s') }}</td><td>
                @can('update', $client)<form method="POST" action="{{ route('admin.clients.revision', [$client, $revision]) }}">@csrf<input type="hidden" name="content_version" value="{{ $client->content_version }}"><x-admin.button type="submit" variant="secondary">Restore client revision #{{ $revision->id }}</x-admin.button></form>@else Read only @endcan
            </td></tr>@empty<tr><td colspan="3">No revisions yet. Saving creates a revision.</td></tr>@endforelse
        </x-admin.table><p class="helper">Restoring creates a new version of the title, sections, image and display order. The current path and existing source fields are retained.</p>
        <div class="pagination">{{ $revisions->links() }}</div>
        @can('delete', $client)<x-admin.glass-card strong><h2>Archive draft</h2><p class="helper">The path and revision history remain reserved after archiving.</p><form method="POST" action="{{ route('admin.clients.archive', $client) }}">@csrf @method('DELETE')<input type="hidden" name="content_version" value="{{ $client->content_version }}"><x-admin.button type="submit" variant="secondary">Archive client draft</x-admin.button></form></x-admin.glass-card>@endcan
        @can('restore', $client)<x-admin.glass-card strong><h2>Restore draft</h2><form method="POST" action="{{ route('admin.clients.restore', $client) }}">@csrf<input type="hidden" name="content_version" value="{{ $client->content_version }}"><x-admin.button type="submit">Restore client draft</x-admin.button></form></x-admin.glass-card>@endcan
    @endif
</x-admin.layout>
