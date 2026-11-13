<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ArchiveBlogTerm;
use App\Actions\Admin\RestoreBlogTermRevision;
use App\Actions\Admin\SaveBlogTerm;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogTermDraftRequest;
use App\Http\Requests\Admin\BlogTermIndexRequest;
use App\Http\Requests\Admin\BlogTermMutationRequest;
use App\Models\Category;
use App\Models\Revision;
use App\Models\Tag;
use App\Services\Content\BlogTerms;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BlogTermController extends Controller
{
    public function index(BlogTermIndexRequest $request, string $kind, BlogTerms $terms): View
    {
        $query = $terms->model($kind)->newQuery();
        if ($request->input('state') === 'archived') {
            $query->onlyTrashed();
        }
        if ($request->filled('q')) {
            $query->where(fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%')->orWhere('path', 'like', '%'.$request->string('q').'%'));
        }

        return view('admin.blog-terms.index', ['kind' => $kind, 'model' => $terms->model($kind), 'items' => $query->orderBy('name')->orderBy('id')->paginate(20)->withQueryString()]);
    }

    public function create(string $kind, BlogTerms $terms): View
    {
        $term = $terms->model($kind);
        Gate::authorize('create', $term::class);

        return $this->form($kind, $term);
    }

    public function edit(string $kind, int $term, BlogTerms $terms): View
    {
        $entry = $terms->find($kind, $term, true);
        Gate::authorize('view', $entry);

        return $this->form($kind, $entry);
    }

    private function form(string $kind, Category|Tag $term): View
    {
        return view('admin.blog-terms.edit', ['kind' => $kind, 'term' => $term,
            'parents' => $kind === 'categories' ? ['' => 'No parent'] + Category::query()->where('id', '!=', $term->id ?? 0)->orderBy('name')
                ->get(['id', 'name'])->mapWithKeys(fn (Category $parent) => [$parent->id => '#'.$parent->id.' '.$parent->name])->all() : [],
            'revisions' => $term->exists ? Revision::query()->where('revisionable_type', $term::class)->where('revisionable_id', $term->id)
                ->where('snapshot->kind', 'taxonomy')->latest('id')->paginate(10) : null]);
    }

    public function store(BlogTermDraftRequest $request, string $kind, BlogTerms $terms, SaveBlogTerm $action): RedirectResponse
    {
        $term = $action->handle($terms->model($kind), $request->validated());

        return redirect()->route('admin.blog.terms.edit', ['kind' => $kind, 'term' => $term->id])->with('status', 'Blog term created.');
    }

    public function update(BlogTermDraftRequest $request, string $kind, int $term, BlogTerms $terms, SaveBlogTerm $action): RedirectResponse
    {
        $action->handle($terms->find($kind, $term), $request->validated());

        return redirect()->route('admin.blog.terms.edit', ['kind' => $kind, 'term' => $term])->with('status', 'Blog term saved.');
    }

    public function archive(BlogTermMutationRequest $request, string $kind, int $term, BlogTerms $terms, ArchiveBlogTerm $action): RedirectResponse
    {
        $action->handle($terms->find($kind, $term), $request->integer('content_version'));

        return redirect()->route('admin.blog.terms.index', $kind)->with('status', 'Blog term archived.');
    }

    public function restore(BlogTermMutationRequest $request, string $kind, int $term, BlogTerms $terms, ArchiveBlogTerm $action): RedirectResponse
    {
        $action->handle($terms->find($kind, $term, true), $request->integer('content_version'), true);

        return redirect()->route('admin.blog.terms.edit', ['kind' => $kind, 'term' => $term])->with('status', 'Blog term restored.');
    }

    public function revision(BlogTermMutationRequest $request, string $kind, int $term, Revision $revision, BlogTerms $terms, RestoreBlogTermRevision $action): RedirectResponse
    {
        $action->handle($terms->find($kind, $term), $revision, $request->integer('content_version'));

        return redirect()->route('admin.blog.terms.edit', ['kind' => $kind, 'term' => $term])->with('status', 'Blog term revision restored.');
    }
}
