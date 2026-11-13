<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ArchivePageDraft;
use App\Actions\Admin\ChangePagePublication;
use App\Actions\Admin\RestorePageRevision;
use App\Actions\Admin\SavePageDraft;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageDraftRequest;
use App\Http\Requests\Admin\PageIndexRequest;
use App\Http\Requests\Admin\PageMutationRequest;
use App\Http\Requests\Admin\PagePublicationRequest;
use App\Models\Media;
use App\Models\Page;
use App\Models\Revision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(PageIndexRequest $request): View
    {
        $query = $request->input('state') === 'archived' ? Page::onlyTrashed() : Page::query();
        if (in_array($request->input('state'), ['draft', 'published', 'scheduled'], true)) {
            $query->where('status', $request->input('state'));
        }
        if ($request->filled('q')) {
            $query->where(function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where('title', 'like', $term)->orWhere('path', 'like', $term)->orWhere('archived_path', 'like', $term);
            });
        }

        return view('admin.pages.index', ['items' => $query->latest('id')->paginate(20)->withQueryString()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Page::class);

        return $this->form(new Page(['status' => 'draft', 'blocks' => [], 'content_version' => 1]));
    }

    public function edit(Page $page): View
    {
        Gate::authorize('view', $page);

        return $this->form($page);
    }

    private function form(Page $page): View
    {
        return view('admin.pages.edit', ['page' => $page, 'publicationHistory' => $page->exists ? Revision::query()->where('revisionable_type', Page::class)
            ->where('revisionable_id', $page->id)->where('snapshot->kind', 'publication')->latest('id')->limit(5)->get() : collect(), 'revisions' => $page->exists
            ? Revision::query()->where('revisionable_type', Page::class)->where('revisionable_id', $page->id)
                ->where(fn ($query) => $query->whereNull('snapshot->kind')->orWhere('snapshot->kind', 'content'))->latest('id')->paginate(10) : null,
            'images' => Media::query()->where('processing_status', 'ready')->whereIn('mime', ['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                ->orderBy('id')->get(['id', 'title', 'original_name'])->mapWithKeys(fn (Media $image) => [$image->id => '#'.$image->id.' '.($image->title ?: $image->original_name)])->all()]);
    }

    public function store(PageDraftRequest $request, SavePageDraft $action): RedirectResponse
    {
        $page = $action->handle($request->validated());

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Page draft created.');
    }

    public function update(PageDraftRequest $request, Page $page, SavePageDraft $action): RedirectResponse
    {
        $action->handle($request->validated(), $page);

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Page draft saved.');
    }

    public function archive(PageMutationRequest $request, Page $page, ArchivePageDraft $action): RedirectResponse
    {
        $action->handle($page, $request->integer('content_version'));

        return redirect()->route('admin.pages.index')->with('status', 'Page draft archived.');
    }

    public function publication(PagePublicationRequest $request, Page $page, ChangePagePublication $action): RedirectResponse
    {
        $action->handle($page, $request->validated());

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Publication state updated.');
    }

    public function restore(PageMutationRequest $request, Page $page, ArchivePageDraft $action): RedirectResponse
    {
        $action->handle($page, $request->integer('content_version'), true);

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Page draft restored.');
    }

    public function revision(PageMutationRequest $request, Page $page, Revision $revision, RestorePageRevision $action): RedirectResponse
    {
        $action->handle($page, $revision, $request->integer('content_version'));

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Revision content restored as a new draft version.');
    }
}
