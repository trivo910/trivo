<x-admin.layout :title="$menu->exists ? 'Menu details' : 'Create menu'">
    <div class="page-intro"><h2>{{ $menu->exists ? $menu->name : 'New navigation menu' }}</h2><p class="helper">Set link order and parents using the forms below. Public navigation rendering is still in development.</p></div>
    <div><x-admin.button :href="route('admin.menus.index')" variant="secondary">Back to menus</x-admin.button></div>
    @if(!$menu->exists || auth()->user()->can('update', $menu))
        <x-admin.glass-card strong><h2>Menu information</h2><form method="POST" action="{{ $menu->exists ? route('admin.menus.update', $menu) : route('admin.menus.store') }}" class="form-stack">@csrf
            @if($menu->exists) @method('PUT')<input type="hidden" name="content_version" value="{{ old('content_version', $menu->content_version) }}">@endif
            <div class="form-grid"><x-admin.input name="name" label="Menu name" :value="old('name', $menu->name)" maxlength="150" required />
                <x-admin.input name="location" label="Menu location" :value="old('location', $menu->location)" maxlength="64" required help="Use a unique lowercase key, such as header or footer. Imported locations cannot be changed." /></div>
            <div><x-admin.button type="submit">Save menu</x-admin.button></div>
        </form></x-admin.glass-card>
    @else
        <x-admin.glass-card strong><h2>Menu information</h2><p>{{ $menu->location }} ? Version {{ $menu->content_version }} ? {{ $menu->trashed() ? 'Archived' : 'Read only' }}</p></x-admin.glass-card>
    @endif
    @if($menu->exists)
        @can('update', $menu)
            <x-admin.glass-card strong><h2>{{ $item ? 'Edit menu item' : 'Add menu item' }}</h2>
                @if($item)<p><x-admin.button :href="route('admin.menus.edit', $menu)" variant="secondary">Add another item</x-admin.button></p>@endif
                <form method="POST" action="{{ $item ? route('admin.menus.items.update', [$menu, $item]) : route('admin.menus.items.store', $menu) }}" class="form-stack">@csrf
                    @if($item) @method('PUT') @endif<input type="hidden" name="content_version" value="{{ old('content_version', $menu->content_version) }}">
                    <div class="form-grid"><x-admin.input name="label" label="Link label" :value="old('label', $item?->label)" maxlength="150" required />
                        <x-admin.input name="url" label="Link URL" :value="old('url', $item?->url)" maxlength="1000" required help="Use a site-relative path, HTTPS URL or #section anchor. Links are never fetched during editing." />
                        <x-admin.select name="parent_id" label="Parent item" :value="old('parent_id', $item?->parent_id ?? '')" :options="$parents" help="Up to five levels; parents must belong to this menu." />
                        <x-admin.input name="sort" label="Display order" type="number" :value="old('sort', $item?->sort ?? 0)" min="0" max="10000" required help="Lower numbers appear first; ties use item ID." />
                        <x-admin.select name="target" label="Open link" :value="old('target', $item?->target ?? '')" :options="['' => 'Default window', '_self' => 'Same window', '_blank' => 'New window']" /></div>
                    <div><x-admin.button type="submit">Save menu item</x-admin.button></div>
                </form>
            </x-admin.glass-card>
        @endcan
        <x-admin.table caption="Menu items"><x-slot:head><tr><th scope="col">Link</th><th scope="col">Parent</th><th scope="col">Order</th><th scope="col">Actions</th></tr></x-slot:head>
            @forelse($items as $entry)<tr><td>{{ $entry->label }}<br><span class="helper">{{ $entry->url }}</span></td><td>{{ $entry->parent_id ? '#'.$entry->parent_id : 'Top level' }}</td><td>{{ $entry->sort }}</td><td>
                @can('update', $menu)<a href="{{ route('admin.menus.edit', ['menu' => $menu, 'item' => $entry->id]) }}">Edit menu item #{{ $entry->id }}</a>@endcan
                @if($entry->legacy_wp_id === null) @can('archiveItem', $menu)<form method="POST" action="{{ route('admin.menus.items.archive', [$menu, $entry]) }}">@csrf @method('DELETE')<input type="hidden" name="content_version" value="{{ $menu->content_version }}"><x-admin.button type="submit" variant="secondary">Archive item #{{ $entry->id }}</x-admin.button></form>@endcan @endif
            </td></tr>@empty<tr><td colspan="4">No links yet. Add a menu item above.</td></tr>@endforelse
        </x-admin.table>
        <x-admin.table caption="Archived menu items"><x-slot:head><tr><th scope="col">Link</th><th scope="col">Action</th></tr></x-slot:head>
            @forelse($archived as $entry)<tr><td>{{ $entry->label }}</td><td>@if($entry->legacy_wp_id === null) @can('archiveItem', $menu)<form method="POST" action="{{ route('admin.menus.items.restore', [$menu, $entry]) }}">@csrf<input type="hidden" name="content_version" value="{{ $menu->content_version }}"><x-admin.button type="submit" variant="secondary">Restore item #{{ $entry->id }}</x-admin.button></form>@endcan @endif</td></tr>
            @empty<tr><td colspan="2">No archived links.</td></tr>@endforelse
        </x-admin.table><div class="pagination">{{ $archived->links() }}</div>
        <x-admin.table caption="Recent menu changes"><x-slot:head><tr><th scope="col">Version</th><th scope="col">Event</th><th scope="col">Saved (UTC)</th></tr></x-slot:head>
            @forelse($history as $revision)<tr><td>{{ $revision->snapshot['content_version'] }}</td><td>{{ $revision->snapshot['event'] ?? 'Menu change' }}</td><td>{{ $revision->created_at->format('Y-m-d H:i:s') }}</td></tr>@empty<tr><td colspan="3">No history yet.</td></tr>@endforelse
        </x-admin.table>
        @can('delete', $menu)<x-admin.glass-card strong><h2>Archive menu</h2><p class="helper">The location, links and change history remain reserved. Archiving is reversible.</p><form method="POST" action="{{ route('admin.menus.archive', $menu) }}">@csrf @method('DELETE')<input type="hidden" name="content_version" value="{{ $menu->content_version }}"><x-admin.button type="submit" variant="secondary">Archive menu</x-admin.button></form></x-admin.glass-card>@endcan
        @can('restore', $menu)<x-admin.glass-card strong><h2>Restore menu</h2><form method="POST" action="{{ route('admin.menus.restore', $menu) }}">@csrf<input type="hidden" name="content_version" value="{{ $menu->content_version }}"><x-admin.button type="submit">Restore menu</x-admin.button></form></x-admin.glass-card>@endcan
    @endif
</x-admin.layout>
