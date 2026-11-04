<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ArchiveServiceDraft;
use App\Actions\Admin\ChangeServicePublication;
use App\Actions\Admin\RestoreServiceRevision;
use App\Actions\Admin\SaveServiceDraft;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceDraftRequest;
use App\Http\Requests\Admin\ServiceIndexRequest;
use App\Http\Requests\Admin\ServiceMutationRequest;
use App\Http\Requests\Admin\ServicePublicationRequest;
use App\Models\Media;
use App\Models\Revision;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(ServiceIndexRequest $request): View
    {
        $query = $request->input('state') === 'archived' ? Service::onlyTrashed() : Service::query();
        if (in_array($request->input('state'), ['draft', 'published', 'scheduled'], true)) {
            $query->where('status', $request->input('state'));
        }
        if ($request->filled('q')) {
            $query->where(fn ($query) => $query->where('title', 'like', '%'.$request->string('q').'%')->orWhere('path', 'like', '%'.$request->string('q').'%'));
        }

        return view('admin.services.index', ['items' => $query->orderBy('sort')->orderBy('id')->paginate(20)->withQueryString()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Service::class);

        return $this->form(new Service(['status' => 'draft', 'blocks' => [], 'sort' => 0, 'content_version' => 1]));
    }

    public function edit(Service $service): View
    {
        Gate::authorize('view', $service);

        return $this->form($service);
    }

    private function form(Service $service): View
    {
        return view('admin.services.edit', ['service' => $service, 'publicationHistory' => $service->exists ? Revision::query()->where('revisionable_type', Service::class)
            ->where('revisionable_id', $service->id)->where('snapshot->kind', 'publication')->latest('id')->limit(5)->get() : collect(), 'revisions' => $service->exists ? Revision::query()
            ->where('revisionable_type', Service::class)->where('revisionable_id', $service->id)->where('snapshot->kind', 'content')->latest('id')->paginate(10) : null,
            'images' => Media::query()->where('processing_status', 'ready')->whereIn('mime', ['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                ->orderBy('id')->get(['id', 'title', 'original_name'])->mapWithKeys(fn (Media $image) => [$image->id => '#'.$image->id.' '.($image->title ?: $image->original_name)])->all()]);
    }

    public function store(ServiceDraftRequest $request, SaveServiceDraft $action): RedirectResponse
    {
        $service = $action->handle($request->validated());

        return redirect()->route('admin.services.edit', $service)->with('status', 'Service draft created.');
    }

    public function update(ServiceDraftRequest $request, Service $service, SaveServiceDraft $action): RedirectResponse
    {
        $action->handle($request->validated(), $service);

        return redirect()->route('admin.services.edit', $service)->with('status', 'Service draft saved.');
    }

    public function archive(ServiceMutationRequest $request, Service $service, ArchiveServiceDraft $action): RedirectResponse
    {
        $action->handle($service, $request->integer('content_version'));

        return redirect()->route('admin.services.index')->with('status', 'Service draft archived.');
    }

    public function publication(ServicePublicationRequest $request, Service $service, ChangeServicePublication $action): RedirectResponse
    {
        $action->handle($service, $request->validated());

        return redirect()->route('admin.services.edit', $service)->with('status', 'Publication state updated.');
    }

    public function restore(ServiceMutationRequest $request, Service $service, ArchiveServiceDraft $action): RedirectResponse
    {
        $action->handle($service, $request->integer('content_version'), true);

        return redirect()->route('admin.services.edit', $service)->with('status', 'Service draft restored.');
    }

    public function revision(ServiceMutationRequest $request, Service $service, Revision $revision, RestoreServiceRevision $action): RedirectResponse
    {
        $action->handle($service, $revision, $request->integer('content_version'));

        return redirect()->route('admin.services.edit', $service)->with('status', 'Service revision restored as a new draft version.');
    }
}
