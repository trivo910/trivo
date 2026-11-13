<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\RestoreAuthorSeo;
use App\Actions\Admin\SaveAuthorSeo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuthorSeoRequest;
use App\Http\Requests\Admin\AuthorSeoRevisionRequest;
use App\Models\Author;
use App\Models\Media;
use App\Models\Revision;
use App\Services\Content\BlogPostSeo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AuthorSeoController extends Controller
{
    public function edit(Author $author, BlogPostSeo $seo): View
    {
        Gate::authorize('updateSeo', $author);

        return view('admin.authors.seo', ['author' => $author, 'seo' => $seo->forForm($author), 'preview' => ['title' => $author->seo_title ?: $author->name, 'description' => $author->seo_description ?: '', 'url' => $author->canonical_url ?: rtrim(config('trivo.canonical_url'), '/').$author->path, 'fallback_title' => $author->name, 'fallback_url' => rtrim(config('trivo.canonical_url'), '/').$author->path], 'revisions' => Revision::query()->where('revisionable_type', Author::class)->where('revisionable_id', $author->id)->where('snapshot->kind', 'seo')->latest('id')->paginate(10), 'images' => Media::query()->where('processing_status', 'ready')->whereIn('mime', ['image/jpeg', 'image/png', 'image/webp', 'image/avif'])->orderBy('id')->get(['id', 'title', 'original_name'])->mapWithKeys(fn (Media $image) => [$image->id => '#'.$image->id.' '.($image->title ?: $image->original_name)])->all()]);
    }

    public function update(AuthorSeoRequest $request, Author $author, SaveAuthorSeo $action): RedirectResponse
    {
        $action->handle($author, $request->validated());

        return redirect()->route('admin.authors.seo', $author)->with('status', 'Author SEO saved.');
    }

    public function restore(AuthorSeoRevisionRequest $request, Author $author, Revision $revision, RestoreAuthorSeo $action): RedirectResponse
    {
        $action->handle($author, $revision, $request->integer('content_version'));

        return redirect()->route('admin.authors.seo', $author)->with('status', 'SEO revision restored as a new version.');
    }
}
