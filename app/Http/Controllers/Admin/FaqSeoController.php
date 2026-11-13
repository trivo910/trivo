<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\RestoreFaqSeo;
use App\Actions\Admin\SaveFaqSeo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqSeoRequest;
use App\Http\Requests\Admin\FaqSeoRevisionRequest;
use App\Models\Faq;
use App\Models\Media;
use App\Models\Revision;
use App\Services\Content\ContentSeo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FaqSeoController extends Controller
{
    public function edit(Faq $faq, ContentSeo $seo): View
    {
        Gate::authorize('updateSeo', $faq);
        $values = $seo->forForm($faq);
        $fallback = $faq->path ? rtrim(config('trivo.canonical_url'), '/').$faq->path : rtrim(config('trivo.canonical_url'), '/');
        return view('admin.faqs.seo', ['faq' => $faq, 'seo' => $values, 'schemaOptions' => $seo->schemaOptions('faq'), 'preview' => ['title' => ($values['seo_title'] ?? null) ?: $faq->title, 'description' => ($values['seo_description'] ?? null) ?: '', 'url' => ($values['canonical_url'] ?? null) ?: $fallback, 'fallback_title' => $faq->title, 'fallback_url' => $fallback], 'revisions' => Revision::query()->where('revisionable_type', Faq::class)->where('revisionable_id', $faq->id)->where('snapshot->kind', 'seo')->latest('id')->paginate(10), 'images' => Media::query()->where('processing_status', 'ready')->whereIn('mime', ['image/jpeg', 'image/png', 'image/webp', 'image/avif'])->orderBy('id')->get(['id', 'title', 'original_name'])->mapWithKeys(fn (Media $image) => [$image->id => '#'.$image->id.' '.($image->title ?: $image->original_name)])->all()]);
    }
    public function update(FaqSeoRequest $request, Faq $faq, SaveFaqSeo $action): RedirectResponse { $action->handle($faq, $request->validated()); return redirect()->route('admin.faqs.seo', $faq)->with('status', 'FAQ SEO saved.'); }
    public function restore(FaqSeoRevisionRequest $request, Faq $faq, Revision $revision, RestoreFaqSeo $action): RedirectResponse { $action->handle($faq, $revision, $request->integer('content_version')); return redirect()->route('admin.faqs.seo', $faq)->with('status', 'SEO revision restored as a new version.'); }
}
