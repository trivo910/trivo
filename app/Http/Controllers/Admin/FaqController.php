<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ArchiveFaqDraft;
use App\Actions\Admin\RestoreFaqRevision;
use App\Actions\Admin\SaveFaqDraft;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqDraftRequest;
use App\Http\Requests\Admin\FaqIndexRequest;
use App\Http\Requests\Admin\FaqMutationRequest;
use App\Models\Faq;
use App\Models\Media;
use App\Models\Revision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(FaqIndexRequest $request): View
    {
        $query = $request->input('state') === 'archived' ? Faq::onlyTrashed() : Faq::query();
        if (in_array($request->input('state'), ['draft', 'published', 'scheduled'], true)) {
            $query->where('status', $request->input('state'));
        }
        if ($request->filled('q')) {
            $query->where(fn ($query) => $query->where('title', 'like', '%'.$request->string('q').'%')->orWhere('path', 'like', '%'.$request->string('q').'%'));
        }

        return view('admin.faqs.index', ['items' => $query->orderBy('sort')->orderBy('id')->paginate(20)->withQueryString()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Faq::class);

        return $this->form(new Faq(['status' => 'draft', 'blocks' => [], 'sort' => 0, 'content_version' => 1]));
    }

    public function edit(Faq $faq): View
    {
        Gate::authorize('view', $faq);

        return $this->form($faq);
    }

    private function form(Faq $faq): View
    {
        return view('admin.faqs.edit', ['faq' => $faq, 'revisions' => $faq->exists ? Revision::query()->where('revisionable_type', Faq::class)
            ->where('revisionable_id', $faq->id)->where('snapshot->kind', 'content')->latest('id')->paginate(10) : null,
            'images' => Media::query()->where('processing_status', 'ready')->whereIn('mime', ['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                ->orderBy('id')->get(['id', 'title', 'original_name'])->mapWithKeys(fn (Media $image) => [$image->id => '#'.$image->id.' '.($image->title ?: $image->original_name)])->all()]);
    }

    public function store(FaqDraftRequest $request, SaveFaqDraft $action): RedirectResponse
    {
        $faq = $action->handle($request->validated());

        return redirect()->route('admin.faqs.edit', $faq)->with('status', 'FAQ draft created.');
    }

    public function update(FaqDraftRequest $request, Faq $faq, SaveFaqDraft $action): RedirectResponse
    {
        $action->handle($request->validated(), $faq);

        return redirect()->route('admin.faqs.edit', $faq)->with('status', 'FAQ draft saved.');
    }

    public function archive(FaqMutationRequest $request, Faq $faq, ArchiveFaqDraft $action): RedirectResponse
    {
        $action->handle($faq, $request->integer('content_version'));

        return redirect()->route('admin.faqs.index')->with('status', 'FAQ draft archived.');
    }

    public function restore(FaqMutationRequest $request, Faq $faq, ArchiveFaqDraft $action): RedirectResponse
    {
        $action->handle($faq, $request->integer('content_version'), true);

        return redirect()->route('admin.faqs.edit', $faq)->with('status', 'FAQ draft restored.');
    }

    public function revision(FaqMutationRequest $request, Faq $faq, Revision $revision, RestoreFaqRevision $action): RedirectResponse
    {
        $action->handle($faq, $revision, $request->integer('content_version'));

        return redirect()->route('admin.faqs.edit', $faq)->with('status', 'FAQ revision restored as a new draft version.');
    }
}
