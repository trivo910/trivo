<x-admin.layout title="404 log">
    <div class="page-intro"><h2>Missing URLs</h2><p class="helper">Queries, referrers, IP addresses and admin paths are excluded. Review the intended destination before creating a redirect.</p></div>
    <x-admin.table caption="Missing public paths"><x-slot:head><tr><th scope="col">Path</th><th scope="col">Hits</th><th scope="col">Last seen (UTC)</th><th scope="col">Action</th></tr></x-slot:head>
        @forelse($logs as $log)<tr><td class="path-cell">{{ $log->path }}</td><td>{{ $log->count }}</td><td>{{ $log->last_seen->format('Y-m-d H:i:s') }}</td><td>@if(\App\Support\RedirectPaths::source($log->path) && !str_contains($log->path, '[email-redacted]'))<x-admin.button variant="secondary" :href="route('admin.redirects.create', ['not_found' => $log->id])">Create redirect</x-admin.button>@else Review manually @endif</td></tr>
        @empty<tr><td colspan="4">No missing paths recorded yet.</td></tr>@endforelse
    </x-admin.table>
    <div class="pagination">{{ $logs->links() }}</div>
</x-admin.layout>
