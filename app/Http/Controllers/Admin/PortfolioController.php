<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ArchivePortfolioDraft;
use App\Actions\Admin\ChangePortfolioPublication;
use App\Actions\Admin\RestorePortfolioRevision;
use App\Actions\Admin\SavePortfolioDraft;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PortfolioDraftRequest;
use App\Http\Requests\Admin\PortfolioIndexRequest;
use App\Http\Requests\Admin\PortfolioMutationRequest;
use App\Http\Requests\Admin\PortfolioPublicationRequest;
use App\Models\Media;
use App\Models\PortfolioItem;
use App\Models\Revision;
use App\Services\Content\PortfolioLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(PortfolioIndexRequest $request): View
    {
        $query = $request->input('state') === 'archived' ? PortfolioItem::onlyTrashed() : PortfolioItem::query();
        if (in_array($request->input('state'), ['draft', 'published', 'scheduled'], true)) {
            $query->where('status', $request->input('state'));
        }
        if ($request->filled('q')) {
            $query->where(fn ($query) => $query->where('title', 'like', '%'.$request->string('q').'%')->orWhere('path', 'like', '%'.$request->string('q').'%'));
        }

        return view('admin.portfolio.index', ['items' => $query->orderBy('sort')->orderBy('id')->paginate(20)->withQueryString()]);
    }

    public function create(): View
    {
        Gate::authorize('create', PortfolioItem::class);

        return $this->form(new PortfolioItem(['status' => 'draft', 'blocks' => [], 'sort' => 0, 'content_version' => 1,
            'details' => ['case_study' => app(PortfolioLayout::class)->blank()]]));
    }

    public function edit(PortfolioItem $portfolio): View
    {
        Gate::authorize('view', $portfolio);

        return $this->form($portfolio);
    }

    private function form(PortfolioItem $portfolio): View
    {
        return view('admin.portfolio.edit', ['portfolio' => $portfolio, 'publicationHistory' => $portfolio->exists ? Revision::query()->where('revisionable_type', PortfolioItem::class)
            ->where('revisionable_id', $portfolio->id)->where('snapshot->kind', 'publication')->latest('id')->limit(5)->get() : collect(), 'revisions' => $portfolio->exists ? Revision::query()->where('revisionable_type', PortfolioItem::class)
            ->where('revisionable_id', $portfolio->id)->where('snapshot->kind', 'content')->latest('id')->paginate(10) : null,
            'images' => Media::query()->where('processing_status', 'ready')->whereIn('mime', ['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                ->orderBy('id')->get(['id', 'title', 'original_name'])->mapWithKeys(fn (Media $image) => [$image->id => '#'.$image->id.' '.($image->title ?: $image->original_name)])->all()]);
    }

    public function store(PortfolioDraftRequest $request, SavePortfolioDraft $action): RedirectResponse
    {
        $portfolio = $action->handle($request->validated());

        return redirect()->route('admin.portfolio.edit', $portfolio)->with('status', 'Portfolio draft created.');
    }

    public function update(PortfolioDraftRequest $request, PortfolioItem $portfolio, SavePortfolioDraft $action): RedirectResponse
    {
        $action->handle($request->validated(), $portfolio);

        return redirect()->route('admin.portfolio.edit', $portfolio)->with('status', $portfolio->status === 'published' ? 'Published portfolio updated.' : 'Portfolio draft saved.');
    }

    public function archive(PortfolioMutationRequest $request, PortfolioItem $portfolio, ArchivePortfolioDraft $action): RedirectResponse
    {
        $action->handle($portfolio, $request->integer('content_version'));

        return redirect()->route('admin.portfolio.index')->with('status', 'Portfolio draft archived.');
    }

    public function publication(PortfolioPublicationRequest $request, PortfolioItem $portfolio, ChangePortfolioPublication $action): RedirectResponse
    {
        $action->handle($portfolio, $request->validated());

        return redirect()->route('admin.portfolio.edit', $portfolio)->with('status', 'Publication state updated.');
    }

    public function restore(PortfolioMutationRequest $request, PortfolioItem $portfolio, ArchivePortfolioDraft $action): RedirectResponse
    {
        $action->handle($portfolio, $request->integer('content_version'), true);

        return redirect()->route('admin.portfolio.edit', $portfolio)->with('status', 'Portfolio draft restored.');
    }

    public function revision(PortfolioMutationRequest $request, PortfolioItem $portfolio, Revision $revision, RestorePortfolioRevision $action): RedirectResponse
    {
        $action->handle($portfolio, $revision, $request->integer('content_version'));

        return redirect()->route('admin.portfolio.edit', $portfolio)->with('status', 'Portfolio revision restored as a new version.');
    }
}
