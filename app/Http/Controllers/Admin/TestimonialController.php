<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ArchiveTestimonialDraft;
use App\Actions\Admin\RestoreTestimonialRevision;
use App\Actions\Admin\SaveTestimonialDraft;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TestimonialDraftRequest;
use App\Http\Requests\Admin\TestimonialIndexRequest;
use App\Http\Requests\Admin\TestimonialMutationRequest;
use App\Models\Media;
use App\Models\Revision;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(TestimonialIndexRequest $request): View
    {
        $query = $request->input('state') === 'archived' ? Testimonial::onlyTrashed() : Testimonial::query();
        if (in_array($request->input('state'), ['draft', 'published', 'scheduled'], true)) {
            $query->where('status', $request->input('state'));
        }
        if ($request->filled('q')) {
            $query->where(fn ($query) => $query->where('title', 'like', '%'.$request->string('q').'%')->orWhere('path', 'like', '%'.$request->string('q').'%'));
        }

        return view('admin.testimonials.index', ['items' => $query->orderBy('sort')->orderBy('id')->paginate(20)->withQueryString()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Testimonial::class);

        return $this->form(new Testimonial(['status' => 'draft', 'blocks' => [], 'sort' => 0, 'content_version' => 1]));
    }

    public function edit(Testimonial $testimonial): View
    {
        Gate::authorize('view', $testimonial);

        return $this->form($testimonial);
    }

    private function form(Testimonial $testimonial): View
    {
        return view('admin.testimonials.edit', ['testimonial' => $testimonial, 'revisions' => $testimonial->exists ? Revision::query()->where('revisionable_type', Testimonial::class)
            ->where('revisionable_id', $testimonial->id)->where('snapshot->kind', 'content')->latest('id')->paginate(10) : null,
            'images' => Media::query()->where('processing_status', 'ready')->whereIn('mime', ['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                ->orderBy('id')->get(['id', 'title', 'original_name'])->mapWithKeys(fn (Media $image) => [$image->id => '#'.$image->id.' '.($image->title ?: $image->original_name)])->all()]);
    }

    public function store(TestimonialDraftRequest $request, SaveTestimonialDraft $action): RedirectResponse
    {
        $testimonial = $action->handle($request->validated());

        return redirect()->route('admin.testimonials.edit', $testimonial)->with('status', 'Testimonial draft created.');
    }

    public function update(TestimonialDraftRequest $request, Testimonial $testimonial, SaveTestimonialDraft $action): RedirectResponse
    {
        $action->handle($request->validated(), $testimonial);

        return redirect()->route('admin.testimonials.edit', $testimonial)->with('status', 'Testimonial draft saved.');
    }

    public function archive(TestimonialMutationRequest $request, Testimonial $testimonial, ArchiveTestimonialDraft $action): RedirectResponse
    {
        $action->handle($testimonial, $request->integer('content_version'));

        return redirect()->route('admin.testimonials.index')->with('status', 'Testimonial draft archived.');
    }

    public function restore(TestimonialMutationRequest $request, Testimonial $testimonial, ArchiveTestimonialDraft $action): RedirectResponse
    {
        $action->handle($testimonial, $request->integer('content_version'), true);

        return redirect()->route('admin.testimonials.edit', $testimonial)->with('status', 'Testimonial draft restored.');
    }

    public function revision(TestimonialMutationRequest $request, Testimonial $testimonial, Revision $revision, RestoreTestimonialRevision $action): RedirectResponse
    {
        $action->handle($testimonial, $revision, $request->integer('content_version'));

        return redirect()->route('admin.testimonials.edit', $testimonial)->with('status', 'Testimonial revision restored as a new draft version.');
    }
}
