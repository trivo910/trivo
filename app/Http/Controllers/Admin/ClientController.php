<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ArchiveClientDraft;
use App\Actions\Admin\RestoreClientRevision;
use App\Actions\Admin\SaveClientDraft;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClientDraftRequest;
use App\Http\Requests\Admin\ClientIndexRequest;
use App\Http\Requests\Admin\ClientMutationRequest;
use App\Models\Client;
use App\Models\Media;
use App\Models\Revision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(ClientIndexRequest $request): View
    {
        $query = $request->input('state') === 'archived' ? Client::onlyTrashed() : Client::query();
        if (in_array($request->input('state'), ['draft', 'published', 'scheduled'], true)) {
            $query->where('status', $request->input('state'));
        }
        if ($request->filled('q')) {
            $query->where(fn ($query) => $query->where('title', 'like', '%'.$request->string('q').'%')->orWhere('path', 'like', '%'.$request->string('q').'%'));
        }

        return view('admin.clients.index', ['items' => $query->orderBy('sort')->orderBy('id')->paginate(20)->withQueryString()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Client::class);

        return $this->form(new Client(['status' => 'draft', 'blocks' => [], 'sort' => 0, 'content_version' => 1]));
    }

    public function edit(Client $client): View
    {
        Gate::authorize('view', $client);

        return $this->form($client);
    }

    private function form(Client $client): View
    {
        return view('admin.clients.edit', ['client' => $client, 'revisions' => $client->exists ? Revision::query()->where('revisionable_type', Client::class)
            ->where('revisionable_id', $client->id)->where('snapshot->kind', 'content')->latest('id')->paginate(10) : null,
            'images' => Media::query()->where('processing_status', 'ready')->whereIn('mime', ['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                ->orderBy('id')->get(['id', 'title', 'original_name'])->mapWithKeys(fn (Media $image) => [$image->id => '#'.$image->id.' '.($image->title ?: $image->original_name)])->all()]);
    }

    public function store(ClientDraftRequest $request, SaveClientDraft $action): RedirectResponse
    {
        $client = $action->handle($request->validated());

        return redirect()->route('admin.clients.edit', $client)->with('status', 'Client draft created.');
    }

    public function update(ClientDraftRequest $request, Client $client, SaveClientDraft $action): RedirectResponse
    {
        $action->handle($request->validated(), $client);

        return redirect()->route('admin.clients.edit', $client)->with('status', 'Client draft saved.');
    }

    public function archive(ClientMutationRequest $request, Client $client, ArchiveClientDraft $action): RedirectResponse
    {
        $action->handle($client, $request->integer('content_version'));

        return redirect()->route('admin.clients.index')->with('status', 'Client draft archived.');
    }

    public function restore(ClientMutationRequest $request, Client $client, ArchiveClientDraft $action): RedirectResponse
    {
        $action->handle($client, $request->integer('content_version'), true);

        return redirect()->route('admin.clients.edit', $client)->with('status', 'Client draft restored.');
    }

    public function revision(ClientMutationRequest $request, Client $client, Revision $revision, RestoreClientRevision $action): RedirectResponse
    {
        $action->handle($client, $revision, $request->integer('content_version'));

        return redirect()->route('admin.clients.edit', $client)->with('status', 'Client revision restored as a new draft version.');
    }
}
