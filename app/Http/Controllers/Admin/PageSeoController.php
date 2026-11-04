<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\RestorePageSeo;
use App\Actions\Admin\SavePageSeo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageSeoRequest;
use App\Http\Requests\Admin\PageSeoRevisionRequest;
use App\Models\Media;
use App\Models\Page;
use App\Models\Revision;
use App\Services\Content\PageSeo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PageSeoController extends Controller
{
    public function edit(Page $page, PageSeo $seo): View
    {
        Gate::authorize('updateSeo', $page);

        return view('admin.pages.seo', ['page' => $page, 'seo' => $seo->forForm($page),
            'preview' => ['title' => $page->seo_title ?: $page->title, 'description' => $page->seo_description ?: '',
                'url' => $page->canonical_url ?: rtrim(config('trivo.canonical_url'), '/').$page->path,
                'fallback_title' => $page->title, 'fallback_url' => rtrim(config('trivo.canonical_url'), '/').$page->path],
            'revisions' => Revision::query()->where('revisionable_type', Page::class)->where('revisionable_id', $page->id)
                ->where('snapshot->kind', 'seo')->latest('id')->paginate(10),
            'images' => Media::query()->where('processing_status', 'ready')->whereIn('mime', ['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                ->orderBy('id')->get(['id', 'title', 'original_name'])->mapWithKeys(fn (Media $image) => [$image->id => '#'.$image->id.' '.($image->title ?: $image->original_name)])->all()]);
    }

    public function update(PageSeoRequest $request, Page $page, SavePageSeo $action): RedirectResponse
    {
        $action->handle($page, $request->validated());

        return redirect()->route('admin.pages.seo', $page)->with('status', 'Page SEO saved.');
    }

    public function restore(PageSeoRevisionRequest $request, Page $page, Revision $revision, RestorePageSeo $action): RedirectResponse
    {
        $action->handle($page, $revision, $request->integer('content_version'));

        return redirect()->route('admin.pages.seo', $page)->with('status', 'SEO revision restored as a new version.');
    }
}
