<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ArchiveMedia;
use App\Actions\Admin\RestoreMedia;
use App\Actions\Admin\RetryMedia;
use App\Actions\Admin\UpdateMedia;
use App\Actions\Admin\UploadMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaIndexRequest;
use App\Http\Requests\Admin\MediaMutationRequest;
use App\Http\Requests\Admin\UpdateMediaRequest;
use App\Http\Requests\Admin\UploadMediaRequest;
use App\Models\Media;
use App\Services\Media\MediaAssets;
use App\Services\Media\MediaPaths;
use App\Services\Media\MediaUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    public function index(MediaIndexRequest $request): View
    {
        $query = $request->string('state')->toString() === 'archived' ? Media::onlyTrashed() : Media::query();
        if ($request->filled('q')) {
            $query->where(function ($query) use ($request) {
                $query->where('title', 'like', '%'.$request->string('q').'%')->orWhere('original_name', 'like', '%'.$request->string('q').'%');
            });
        }
        if ($request->filled('folder')) {
            $query->where('folder', $request->string('folder')->toString());
        }
        if (in_array($request->string('state')->toString(), ['queued', 'processing', 'failed'], true)) {
            $query->where('processing_status', $request->string('state')->toString());
        }

        return view('admin.media.index', ['items' => $query->latest()->paginate(24)->withQueryString(),
            'folders' => Media::query()->whereNotNull('folder')->distinct()->orderBy('folder')->pluck('folder')]);
    }

    public function create(): View
    {
        Gate::authorize('create', Media::class);

        return view('admin.media.upload');
    }

    public function store(UploadMediaRequest $request, UploadMedia $action): JsonResponse|RedirectResponse
    {
        $file = $request->file('image');
        abort_unless($file instanceof UploadedFile, 422);
        $media = $action->handle($file, $request->safe()->except('image'));
        $url = route('admin.media.show', $media);
        $request->session()->flash('status', $media->wasRecentlyCreated ? 'Upload received. Responsive images will be prepared in the background.' : 'Existing image reused. Its saved metadata has been kept.');

        return $request->expectsJson() ? response()->json(['redirect' => $url, 'status' => $media->processing_status])
            : redirect($url);
    }

    public function show(Media $media, MediaUsage $usage, MediaAssets $assets): View
    {
        Gate::authorize('view', $media);

        return view('admin.media.show', ['media' => $media, 'references' => $usage->references($media),
            'processingMessage' => $assets->message($media), 'variants' => $assets->variants($media)]);
    }

    public function update(UpdateMediaRequest $request, Media $media, UpdateMedia $action): RedirectResponse
    {
        $action->handle($media, $request->validated());

        return redirect()->route('admin.media.show', $media)->with('status', 'Image metadata saved.');
    }

    public function archive(MediaMutationRequest $request, Media $media, ArchiveMedia $action): RedirectResponse
    {
        $action->handle($media);

        return redirect()->route('admin.media.index')->with('status', 'Image archived. Its published files remain available.');
    }

    public function restore(MediaMutationRequest $request, Media $media, RestoreMedia $action): RedirectResponse
    {
        $action->handle($media);

        return redirect()->route('admin.media.show', $media)->with('status', 'Image restored.');
    }

    public function retry(MediaMutationRequest $request, Media $media, RetryMedia $action): RedirectResponse
    {
        $action->handle($media);

        return redirect()->route('admin.media.show', $media)->with('status', 'Image processing requested.');
    }

    public function status(Media $media, MediaAssets $assets): JsonResponse
    {
        Gate::authorize('view', $media);

        return response()->json(['status' => $media->processing_status, 'message' => $assets->message($media)]);
    }

    public function preview(Media $media, MediaAssets $assets, MediaPaths $paths): Response
    {
        Gate::authorize('view', $media);
        abort_unless($media->processing_status === 'ready', 404);
        if ($media->isLegacy()) {
            abort_unless(in_array($media->mime, ['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/gif'], true), 404);

            return redirect($assets->originalUrl($media));
        }
        $paths->directory($media);
        $thumbnail = $assets->variants($media, 'image/webp')[0] ?? ['path' => $media->path, 'mime' => $media->mime];
        $disk = Storage::disk($media->disk);
        abort_unless($disk->exists($thumbnail['path']), 404);

        return $disk->response($thumbnail['path'], null, ['Content-Type' => $thumbnail['mime'], 'X-Content-Type-Options' => 'nosniff']);
    }
}
