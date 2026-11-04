<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\RestorePortfolioSeo;
use App\Actions\Admin\SavePortfolioSeo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PortfolioSeoRequest;
use App\Http\Requests\Admin\PortfolioSeoRevisionRequest;
use App\Models\Media;
use App\Models\PortfolioItem;
use App\Models\Revision;
use App\Services\Content\ContentSeo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PortfolioSeoController extends Controller
{
    public function edit(PortfolioItem $portfolio, ContentSeo $seo): View
    {
        Gate::authorize('updateSeo', $portfolio);
        $values = $seo->forForm($portfolio);
        $fallback = rtrim(config('trivo.canonical_url'), '/').$portfolio->path;

        return view('admin.portfolio.seo', ['portfolio' => $portfolio, 'seo' => $values, 'schemaOptions' => $seo->schemaOptions('portfolio'),
            'preview' => ['title' => ($values['seo_title'] ?? null) ?: $portfolio->title, 'description' => ($values['seo_description'] ?? null) ?: '',
                'url' => ($values['canonical_url'] ?? null) ?: $fallback, 'fallback_title' => $portfolio->title, 'fallback_url' => $fallback],
            'revisions' => Revision::query()->where('revisionable_type', PortfolioItem::class)->where('revisionable_id', $portfolio->id)
                ->where('snapshot->kind', 'seo')->latest('id')->paginate(10),
            'images' => Media::query()->where('processing_status', 'ready')->whereIn('mime', ['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                ->orderBy('id')->get(['id', 'title', 'original_name'])->mapWithKeys(fn (Media $image) => [$image->id => '#'.$image->id.' '.($image->title ?: $image->original_name)])->all()]);
    }

    public function update(PortfolioSeoRequest $request, PortfolioItem $portfolio, SavePortfolioSeo $action): RedirectResponse
    {
        $action->handle($portfolio, $request->validated());

        return redirect()->route('admin.portfolio.seo', $portfolio)->with('status', 'Portfolio SEO saved.');
    }

    public function restore(PortfolioSeoRevisionRequest $request, PortfolioItem $portfolio, Revision $revision, RestorePortfolioSeo $action): RedirectResponse
    {
        $action->handle($portfolio, $revision, $request->integer('content_version'));

        return redirect()->route('admin.portfolio.seo', $portfolio)->with('status', 'SEO revision restored as a new version.');
    }
}
