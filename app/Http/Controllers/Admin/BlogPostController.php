<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ArchiveBlogPost;
use App\Actions\Admin\ChangeBlogPostPublication;
use App\Actions\Admin\RestoreBlogPostRevision;
use App\Actions\Admin\SaveBlogPost;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogPostDraftRequest;
use App\Http\Requests\Admin\BlogPostIndexRequest;
use App\Http\Requests\Admin\BlogPostMutationRequest;
use App\Http\Requests\Admin\BlogPostPublicationRequest;
use App\Models\Author;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\Revision;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function index(BlogPostIndexRequest $request): View
    {
        $state = $request->input('state', 'draft');
        $query = $state === 'archived' ? Post::onlyTrashed() : Post::query()->where('status', $state);
        if ($request->filled('q')) {
            $query->where(fn ($query) => $query->where('title', 'like', '%'.$request->string('q').'%')->orWhere('path', 'like', '%'.$request->string('q').'%'));
        }
        if ($request->input('score') === 'needs_attention') {
            $query->where('seo_score', '<', 50);
        } elseif ($request->input('score') === 'good') {
            $query->where('seo_score', '>=', 80);
        }

        return view('admin.posts.index', ['posts' => $query->orderByDesc('id')->paginate(20)->withQueryString()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Post::class);

        return $this->form(new Post(['status' => 'draft', 'content_version' => 1]));
    }

    public function edit(int $post): View
    {
        $post = Post::query()->withTrashed()->findOrFail($post);
        Gate::authorize('view', $post);

        return $this->form($post);
    }

    private function form(Post $post): View
    {
        $names = fn (string $model) => $model::query()->orderBy('name')->get(['id', 'name'])->mapWithKeys(fn ($row) => [$row->id => '#'.$row->id.' '.$row->name])->all();

        return view('admin.posts.edit', ['post' => $post, 'revisions' => $post->exists ? Revision::query()->where('revisionable_type', Post::class)->where('revisionable_id', $post->id)->where('snapshot->kind', 'post_draft')->latest('id')->paginate(10) : null, 'publicationHistory' => $post->exists ? Revision::query()->where('revisionable_type', Post::class)->where('revisionable_id', $post->id)->where('snapshot->kind', 'publication')->latest('id')->limit(5)->get() : collect(), 'authors' => ['' => 'No author'] + $names(Author::class), 'categories' => ['' => 'No category'] + $names(Category::class),
            'tags' => $names(Tag::class), 'images' => ['' => 'No featured image'] + Media::query()->where('processing_status', 'ready')->whereNotNull('alt')->orderBy('id')->get(['id', 'title', 'original_name'])->mapWithKeys(fn (Media $image) => [$image->id => '#'.$image->id.' '.($image->title ?: $image->original_name)])->all()]);
    }

    public function store(BlogPostDraftRequest $request, SaveBlogPost $action): RedirectResponse
    {
        $post = $action->handle(new Post, $request->validated());

        return redirect()->route('admin.posts.edit', $post)->with('status', 'Post draft created.');
    }

    public function update(BlogPostDraftRequest $request, int $post, SaveBlogPost $action): RedirectResponse
    {
        $action->handle(Post::query()->findOrFail($post), $request->validated());

        return redirect()->route('admin.posts.edit', $post)->with('status', 'Post draft saved.');
    }

    public function archive(BlogPostMutationRequest $request, int $post, ArchiveBlogPost $action): RedirectResponse
    {
        $action->handle(Post::query()->findOrFail($post), $request->integer('content_version'));

        return redirect()->route('admin.posts.index')->with('status', 'Post draft archived.');
    }

    public function restore(BlogPostMutationRequest $request, int $post, ArchiveBlogPost $action): RedirectResponse
    {
        $action->handle(Post::query()->withTrashed()->findOrFail($post), $request->integer('content_version'), true);

        return redirect()->route('admin.posts.edit', $post)->with('status', 'Post draft restored.');
    }

    public function revision(BlogPostMutationRequest $request, int $post, Revision $revision, RestoreBlogPostRevision $action): RedirectResponse
    {
        $action->handle(Post::query()->findOrFail($post), $revision, $request->integer('content_version'));

        return redirect()->route('admin.posts.edit', $post)->with('status', 'Post revision restored.');
    }

    public function publication(BlogPostPublicationRequest $request, Post $post, ChangeBlogPostPublication $action): RedirectResponse
    {
        $action->handle($post, $request->validated());

        return redirect()->route('admin.posts.edit', $post)->with('status', 'Publication state updated.');
    }
}
