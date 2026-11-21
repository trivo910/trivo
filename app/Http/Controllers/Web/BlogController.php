<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Services\Content\BlogPostLayout;
use App\Services\Content\PublicContentRenderer;
use App\Support\ContentHtml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $record = Page::query()->where('status', 'published')->where('path', '/blog')->first();
        $posts = Post::query()->articles()->with(['category', 'author'])->where('status', 'published')
            ->where('published_at', '<=', now())->orderByDesc('published_at')->orderByDesc('id')->paginate(18);
        $media = Media::query()->whereIn('id', $posts->getCollection()->pluck('featured_media_id')->filter())->get()->keyBy('id');
        $posts->through(function (Post $post) use ($media): array {
            $featured = $media->get($post->featured_media_id);

            return [
                'title' => $post->title,
                'excerpt' => $post->excerpt ?: $post->seo_description ?: Str::limit(strip_tags($post->body), 180),
                'category' => $post->category?->name ?? '', 'date' => $post->published_at?->format('F Y') ?? '',
                'slug' => $post->slug, 'url' => url($post->path),
                'featured_media' => $featured?->processing_status === 'ready' ? $featured : null,
                'image' => $featured?->processing_status === 'ready' ? null
                    : app(BlogPostLayout::class)->firstImage(app(ContentHtml::class)->clean($post->body)),
            ];
        });

        return view('pages.blog', ['record' => $record, 'posts' => $posts]);
    }

    public function show(string $slug, PublicContentRenderer $renderer): View|RedirectResponse
    {
        $query = Post::query()->articles()->where('status', 'published')->where('published_at', '<=', now());
        $post = (clone $query)->whereIn('path', ['/blog/'.$slug, '/blog/'.$slug.'/'])->first()
            ?? $query->where('slug', $slug)->orderByDesc('id')->firstOrFail();
        if (rtrim($post->path, '/') !== '/blog/'.$slug) {
            return redirect(url($post->path), 301);
        }

        return $renderer->render($post);
    }
}
