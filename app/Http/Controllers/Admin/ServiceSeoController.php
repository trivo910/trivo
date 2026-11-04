<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\RestoreServiceSeo;
use App\Actions\Admin\SaveServiceSeo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceSeoRequest;
use App\Http\Requests\Admin\ServiceSeoRevisionRequest;
use App\Models\Media;
use App\Models\Revision;
use App\Models\Service;
use App\Services\Content\ContentSeo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ServiceSeoController extends Controller
{
    public function edit(Service $service, ContentSeo $seo): View
    {
        Gate::authorize('updateSeo', $service);
        $values = $seo->forForm($service);
        $fallback = rtrim(config('trivo.canonical_url'), '/').$service->path;

        return view('admin.services.seo', ['service' => $service, 'seo' => $values, 'schemaOptions' => $seo->schemaOptions('service'),
            'preview' => ['title' => ($values['seo_title'] ?? null) ?: $service->title, 'description' => ($values['seo_description'] ?? null) ?: '',
                'url' => ($values['canonical_url'] ?? null) ?: $fallback, 'fallback_title' => $service->title, 'fallback_url' => $fallback],
            'revisions' => Revision::query()->where('revisionable_type', Service::class)->where('revisionable_id', $service->id)
                ->where('snapshot->kind', 'seo')->latest('id')->paginate(10),
            'images' => Media::query()->where('processing_status', 'ready')->whereIn('mime', ['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                ->orderBy('id')->get(['id', 'title', 'original_name'])->mapWithKeys(fn (Media $image) => [$image->id => '#'.$image->id.' '.($image->title ?: $image->original_name)])->all()]);
    }

    public function update(ServiceSeoRequest $request, Service $service, SaveServiceSeo $action): RedirectResponse
    {
        $action->handle($service, $request->validated());

        return redirect()->route('admin.services.seo', $service)->with('status', 'Service SEO saved.');
    }

    public function restore(ServiceSeoRevisionRequest $request, Service $service, Revision $revision, RestoreServiceSeo $action): RedirectResponse
    {
        $action->handle($service, $revision, $request->integer('content_version'));

        return redirect()->route('admin.services.seo', $service)->with('status', 'SEO revision restored as a new version.');
    }
}
