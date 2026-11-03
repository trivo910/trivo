<x-admin.layout :title="$redirect->exists ? 'Edit redirect' : 'Add redirect'">
    <x-admin.glass-card strong>
        <form method="POST" action="{{ $redirect->exists ? route('admin.redirects.update', $redirect) : route('admin.redirects.store') }}" class="form-stack">@csrf
            @if($redirect->exists) @method('PUT') @endif
            <x-admin.input name="from_path" label="Old path" :value="old('from_path', $redirect->from_path)" placeholder="/old-page" required maxlength="768" help="Keep the exact spelling and trailing slash of the old URL." />
            <x-admin.select name="status_code" label="Response status" :options="[301 => '301 — Permanent redirect', 302 => '302 — Temporary redirect', 410 => '410 — Permanently removed']" :value="old('status_code', $redirect->status_code)" />
            <x-admin.input name="to_url" label="Destination" :value="old('to_url', $redirect->to_url)" placeholder="/current-page" maxlength="768" help="Use a public path on this site. Leave blank for 410. Redirect loops and removed destinations are rejected." />
            <div class="component-row"><x-admin.button type="submit">Save redirect</x-admin.button><x-admin.button variant="secondary" :href="route('admin.redirects.index')">Cancel</x-admin.button></div>
        </form>
    </x-admin.glass-card>
    @if($redirect->exists)
        <x-admin.glass-card strong><h2>Delete redirect</h2>
            <x-admin.modal title="Delete this redirect?" label="Delete redirect" id="delete-redirect">
                <p>{{ $redirect->from_path }}</p>
                <form method="POST" action="{{ route('admin.redirects.destroy', $redirect) }}">@csrf @method('DELETE')<x-admin.button type="submit" variant="danger">Confirm deletion</x-admin.button></form>
            </x-admin.modal>
        </x-admin.glass-card>
    @endif
</x-admin.layout>
