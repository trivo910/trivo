<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\DeleteRedirect;
use App\Actions\Admin\ImportRedirects;
use App\Actions\Admin\SaveRedirect;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeleteRedirectRequest;
use App\Http\Requests\Admin\RedirectImportRequest;
use App\Http\Requests\Admin\RedirectRequest;
use App\Models\NotFoundLog;
use App\Models\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RedirectController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Redirect::class);

        return view('admin.redirects.index', ['redirects' => Redirect::query()->orderBy('from_path')->paginate(25)]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Redirect::class);
        $request->validate(['not_found' => ['nullable', 'integer', 'exists:not_found_logs,id']]);
        $log = $request->integer('not_found') ? NotFoundLog::query()->findOrFail($request->integer('not_found')) : null;

        return view('admin.redirects.form', ['redirect' => new Redirect(['from_path' => $log?->path, 'status_code' => 301])]);
    }

    public function edit(Redirect $redirect): View
    {
        Gate::authorize('update', $redirect);

        return view('admin.redirects.form', compact('redirect'));
    }

    public function store(RedirectRequest $request, SaveRedirect $action): RedirectResponse
    {
        $action->handle($request->validated());

        return redirect()->route('admin.redirects.index')->with('status', 'Redirect saved.');
    }

    public function update(RedirectRequest $request, Redirect $redirect, SaveRedirect $action): RedirectResponse
    {
        $action->handle($request->validated(), $redirect);

        return redirect()->route('admin.redirects.index')->with('status', 'Redirect updated.');
    }

    public function destroy(DeleteRedirectRequest $request, Redirect $redirect, DeleteRedirect $action): RedirectResponse
    {
        $action->handle($redirect);

        return redirect()->route('admin.redirects.index')->with('status', 'Redirect deleted.');
    }

    public function import(RedirectImportRequest $request, ImportRedirects $action): RedirectResponse
    {
        $file = $request->file('csv');
        abort_unless($file instanceof UploadedFile, 422);
        $count = $action->handle($file);

        return redirect()->route('admin.redirects.index')->with('status', $count.' redirects imported.');
    }

    public function notFound(): View
    {
        Gate::authorize('viewAny', NotFoundLog::class);

        return view('admin.redirects.not-found', ['logs' => NotFoundLog::query()->orderByDesc('last_seen')->paginate(25)]);
    }
}
