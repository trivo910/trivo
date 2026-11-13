@if($post->exists)
    <x-admin.glass-card strong>
        <h2>Post draft revision history</h2>
        @php($postRevisions = $revisions?->items() ?? [])
        @if($postRevisions === [])
            <p class="helper">No revisions yet. Saving a draft creates a new version.</p>
        @else
            <ul>
                @foreach($postRevisions as $revision)
                    <li>
                        Version {{ $revision->snapshot['content_version'] }} saved {{ $revision->created_at->format('Y-m-d H:i:s') }} UTC
                        @can('update', $post)
                            <form method="POST" action="{{ route('admin.posts.revision', ['post' => $post, 'revision' => $revision]) }}">
                                @csrf
                                <input type="hidden" name="content_version" value="{{ $post->content_version }}">
                                <x-admin.button type="submit" variant="secondary">Restore this revision</x-admin.button>
                            </form>
                        @endcan
                    </li>
                @endforeach
            </ul>
        @endif
    </x-admin.glass-card>
    @can('delete', $post)
        <x-admin.glass-card strong>
            <h2>Archive post draft</h2>
            <p class="helper">Only non-imported drafts can be archived. The path and history remain reserved.</p>
            <form method="POST" action="{{ route('admin.posts.archive', $post) }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="content_version" value="{{ $post->content_version }}">
                <x-admin.button type="submit" variant="secondary">Archive post draft</x-admin.button>
            </form>
        </x-admin.glass-card>
    @endcan
    @can('restore', $post)
        <x-admin.glass-card strong>
            <h2>Restore post draft</h2>
            <form method="POST" action="{{ route('admin.posts.restore', $post) }}">
                @csrf
                <input type="hidden" name="content_version" value="{{ $post->content_version }}">
                <x-admin.button type="submit">Restore post draft</x-admin.button>
            </form>
        </x-admin.glass-card>
    @endcan
@endif
