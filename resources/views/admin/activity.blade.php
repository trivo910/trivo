<x-admin.layout title="Activity log">
    <div class="page-intro"><h2>Account activity</h2><p class="helper">Authentication and administrative events. Credentials and submitted codes are excluded.</p></div>
    <x-admin.table caption="Administrative activity">
        <x-slot:head><tr><th scope="col">Event</th><th scope="col">Account ID</th><th scope="col">Time (UTC)</th></tr></x-slot:head>
        @forelse($activities as $activity)
            <tr><td>{{ str_replace('_', ' ', $activity->event ?? $activity->description) }}</td><td>{{ $activity->causer_id ?? '—' }}</td><td><time datetime="{{ $activity->created_at->toIso8601String() }}">{{ $activity->created_at->format('Y-m-d H:i:s') }}</time></td></tr>
        @empty <tr><td colspan="3">No activity recorded yet.</td></tr> @endforelse
    </x-admin.table>
    <div class="pagination">{{ $activities->links() }}</div>
</x-admin.layout>
