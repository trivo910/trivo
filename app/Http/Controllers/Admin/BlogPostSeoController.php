<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\RestoreBlogPostSeo;
use App\Actions\Admin\SaveBlogPostSeo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogPostSeoRequest;
use App\Http\Requests\Admin\BlogPostSeoRevisionRequest;
use App\Models\Media;
use App\Models\Post;
use App\Models\Revision;
use App\Services\Content\BlogPostSeo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BlogPostSeoController extends Controller
{
    public function edit(Post $post, BlogPostSeo $seo): View
    {
        Gate::authorize('updateSeo', $post);

        return view('admin.posts.seo', ['post' => $post, 'seo' => $seo->forForm($post),
            'preview' => ['title' => $post->seo_title ?: $post->title, 'description' => $post->seo_description ?: '', 'url' => $post->canonical_url ?: rtrim(config('trivo.canonical_url'), '/').$post->path, 'fallback_title' => $post->title, 'fallback_url' => rtrim(config('trivo.canonical_url'), '/').$post->path],
            'revisions' => Revision::query()->where('revisionable_type', Post::class)->where('revisionable_id', $post->id)->where('snapshot->kind', 'seo')->latest('id')->paginate(10),
            'images' => Media::query()->where('processing_status', 'ready')->whereIn('mime', ['image/jpeg', 'image/png', 'image/webp', 'image/avif'])->orderBy('id')->get(['id', 'title', 'original_name'])->mapWithKeys(fn (Media $image) => [$image->id => '#'.$image->id.' '.($image->title ?: $image->original_name)])->all()]);
    }

    public function update(BlogPostSeoRequest $request, Post $post, SaveBlogPostSeo $action): RedirectResponse
    {
        $action->handle($post, $request->validated());

        return redirect()->route('admin.posts.seo', $post)->with('status', 'Post SEO saved.');
    }

    public function restore(BlogPostSeoRevisionRequest $request, Post $post, Revision $revision, RestoreBlogPostSeo $action): RedirectResponse
    {
        $action->handle($post, $revision, $request->integer('content_version'));

        return redirect()->route('admin.posts.seo', $post)->with('status', 'SEO revision restored as a new version.');
    }
}
