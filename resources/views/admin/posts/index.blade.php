<x-admin.layout title="Blog posts">
    <div class="page-intro">
        <h2>Blog posts</h2>
        <p class="helper">Plain-text editing and post publication scheduling are available. Rich-text formatting and public rendering remain in development.</p>
    </div>
    @can('create', \App\Models\Post::class)
        <div><x-admin.button :href="route('admin.posts.create')">Create post draft</x-admin.button></div>
    @endcan
    <x-admin.glass-card strong>
        <form method="GET" action="{{ route('admin.posts.index') }}" class="form-grid">
            <x-admin.input name="q" label="Search posts" :value="request('q')" maxlength="120" />
            <x-admin.select name="state" label="Post state" :value="request('state', 'draft')" :options="['draft' => 'Draft posts', 'scheduled' => 'Scheduled posts', 'published' => 'Published posts', 'archived' => 'Archived posts']" />
            <x-admin.select name="score" label="SEO score" :value="request('score', 'any')" :options="['any' => 'Any score', 'needs_attention' => 'Needs attention (under 50)', 'good' => 'Good (80 or above)']" />
            <div class="filter-actions"><x-admin.button type="submit" variant="secondary">Filter posts</x-admin.button></div>
        </form>
    </x-admin.glass-card>
    <x-admin.table caption="Blog posts">
        <x-slot:head><tr><th scope="col">Title</th><th scope="col">Path</th><th scope="col">SEO</th><th scope="col">State</th></tr></x-slot:head>
        @foreach($posts as $post)
            <tr><td><a href="{{ route('admin.posts.edit', $post) }}">{{ $post->title }}</a></td><td class="path-cell">{{ $post->path }}</td><td>{{ $post->seo_score }}/100</td><td>{{ $post->trashed() ? 'Archived' : ucfirst($post->status) }}</td></tr>
        @endforeach
        @if($posts->isEmpty())
            <tr><td colspan="4">No posts found.</td></tr>
        @endif
    </x-admin.table>
    <div class="pagination">{{ $posts->links() }}</div>
</x-admin.layout>
