@if($post->exists)
    <x-admin.glass-card strong>
        <h2>Post revision history</h2>
        @php($postRevisions = $revisions?->items() ?? [])
        @if($postRevisions === [])
            <p class="helper">No revisions yet. Saving creates a new version.</p>
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
            <h2>Delete post</h2>
            <p class="helper">Move this article to Trash to remove it from the website. Content, revisions and shared images are retained.</p>
            <x-admin.modal title="Move this post to Trash?" label="Delete post">
                <p>The public article will become unavailable. You can restore it as a draft from Trash.</p>
                <form method="POST" action="{{ route('admin.posts.archive', $post) }}">@csrf @method('DELETE')
                    <input type="hidden" name="content_version" value="{{ $post->content_version }}">
                    <x-admin.button type="submit" variant="secondary">Move to Trash</x-admin.button>
                </form>
            </x-admin.modal>
        </x-admin.glass-card>
    @endcan
    @can('restore', $post)
        <x-admin.glass-card strong>
            <h2>Restore post as draft</h2>
            <form method="POST" action="{{ route('admin.posts.restore', $post) }}">
                @csrf
                <input type="hidden" name="content_version" value="{{ $post->content_version }}">
                <x-admin.button type="submit">Restore post as draft</x-admin.button>
            </form>
        </x-admin.glass-card>
    @endcan
@endif
