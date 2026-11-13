<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ArchiveAuthorProfile;
use App\Actions\Admin\RestoreAuthorProfileRevision;
use App\Actions\Admin\SaveAuthorProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuthorProfileDraftRequest;
use App\Http\Requests\Admin\AuthorProfileIndexRequest;
use App\Http\Requests\Admin\AuthorProfileMutationRequest;
use App\Models\Author;
use App\Models\Revision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AuthorProfileController extends Controller
{
    public function index(AuthorProfileIndexRequest $request): View
    {
        $query = Author::query();
        if ($request->input('state') === 'archived') {
            $query->onlyTrashed();
        }
        if ($request->filled('q')) {
            $query->where(fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%')->orWhere('path', 'like', '%'.$request->string('q').'%'));
        }

        return view('admin.authors.index', ['authors' => $query->orderBy('name')->orderBy('id')->paginate(20)->withQueryString()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Author::class);

        return $this->form(new Author);
    }

    public function edit(int $author): View
    {
        $author = Author::query()->withTrashed()->findOrFail($author);
        Gate::authorize('view', $author);

        return $this->form($author);
    }

    private function form(Author $author): View
    {
        return view('admin.authors.edit', ['author' => $author, 'revisions' => $author->exists
            ? Revision::query()->where('revisionable_type', Author::class)->where('revisionable_id', $author->id)
                ->where('snapshot->kind', 'author_profile')->latest('id')->paginate(10) : null]);
    }

    public function store(AuthorProfileDraftRequest $request, SaveAuthorProfile $action): RedirectResponse
    {
        $author = $action->handle(new Author, $request->validated());

        return redirect()->route('admin.authors.edit', $author)->with('status', 'Author profile created.');
    }

    public function update(AuthorProfileDraftRequest $request, int $author, SaveAuthorProfile $action): RedirectResponse
    {
        $action->handle(Author::query()->findOrFail($author), $request->validated());

        return redirect()->route('admin.authors.edit', $author)->with('status', 'Author profile saved.');
    }

    public function archive(AuthorProfileMutationRequest $request, int $author, ArchiveAuthorProfile $action): RedirectResponse
    {
        $action->handle(Author::query()->findOrFail($author), $request->integer('content_version'));

        return redirect()->route('admin.authors.index')->with('status', 'Author profile archived.');
    }

    public function restore(AuthorProfileMutationRequest $request, int $author, ArchiveAuthorProfile $action): RedirectResponse
    {
        $action->handle(Author::query()->withTrashed()->findOrFail($author), $request->integer('content_version'), true);

        return redirect()->route('admin.authors.edit', $author)->with('status', 'Author profile restored.');
    }

    public function revision(AuthorProfileMutationRequest $request, int $author, Revision $revision, RestoreAuthorProfileRevision $action): RedirectResponse
    {
        $action->handle(Author::query()->findOrFail($author), $revision, $request->integer('content_version'));

        return redirect()->route('admin.authors.edit', $author)->with('status', 'Author profile revision restored.');
    }
}
