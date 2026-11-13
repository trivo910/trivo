<x-admin.layout title="Menus">
    <div class="page-intro"><h2>Navigation menus</h2><p class="helper">Manage menu locations, links and hierarchy. Public navigation rendering is still in development.</p></div>
    @can('create', App\Models\Menu::class)<div><x-admin.button :href="route('admin.menus.create')">Create menu</x-admin.button></div>@endcan
    <x-admin.glass-card strong><form method="GET" action="{{ route('admin.menus.index') }}" class="form-grid">
        <x-admin.input name="q" label="Search menus" :value="request('q')" maxlength="120" />
        <x-admin.select name="state" label="Menu state" :value="request('state', 'active')" :options="['active' => 'Active menus', 'archived' => 'Archived menus']" />
        <div class="filter-actions"><x-admin.button type="submit" variant="secondary">Filter menus</x-admin.button></div>
    </form></x-admin.glass-card>
    <x-admin.table caption="Navigation menus"><x-slot:head><tr><th scope="col">Menu</th><th scope="col">Location</th><th scope="col">State</th></tr></x-slot:head>
        @forelse($menus as $menu)<tr><td><a href="{{ route('admin.menus.edit', $menu) }}">{{ $menu->name }}</a></td><td>{{ $menu->location }}</td><td>{{ $menu->trashed() ? 'Archived' : 'Active' }}</td></tr>
        @empty<tr><td colspan="3">No menus found. Create a menu or adjust the filters.</td></tr>@endforelse
    </x-admin.table><div class="pagination">{{ $menus->links() }}</div>
</x-admin.layout>
