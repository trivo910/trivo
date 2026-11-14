<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Post;
use App\Services\Content\PublicContentRenderer;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $record = Page::query()->where('status', 'published')->where('path', '/blog')->first();
        $published = Post::query()->where('status', 'published')->whereNotNull('published_at')
            ->orderByDesc('published_at')->limit(30)->get();
        if ($published->isNotEmpty()) {
            $posts = [];
            foreach ($published as $post) {
                $posts[] = [
                    'title' => $post->title,
                    'excerpt' => $post->excerpt ?: $post->seo_description ?: Str::limit(strip_tags($post->body), 180),
                    'category' => 'Insights',
                    'date' => $post->published_at?->format('F Y') ?? '',
                    'slug' => $post->slug,
                    'url' => $post->path,
                ];
            }

            return view('pages.blog', [
                'posts' => $posts,
                'record' => $record,
            ]);
        }

        return view('pages.blog', [
            'record' => $record,
            'posts' => [
                [
                    'title' => 'How to turn intent into pipeline with SEO-led content',
                    'excerpt' => 'A practical framework for using content strategy to capture high-intent traffic and improve lead quality.',
                    'category' => 'SEO strategy',
                    'date' => 'October 2026',
                    'slug' => 'turn-intent-into-pipeline-seo-led-content',
                ],
                [
                    'title' => 'The paid media dashboard every growth team should track',
                    'excerpt' => 'The essential metrics, conversion signals, and weekly review rhythms that keep ad spend productive.',
                    'category' => 'Marketing ops',
                    'date' => 'September 2026',
                    'slug' => 'paid-media-dashboard-growth-team-track',
                ],
                [
                    'title' => 'What good conversion design actually looks like',
                    'excerpt' => 'Good conversion design reduces friction, supports confidence, and makes the next step obvious.',
                    'category' => 'CRO',
                    'date' => 'August 2026',
                    'slug' => 'what-good-conversion-design-actually-looks-like',
                ],
            ],
        ]);
    }

    public function show(string $slug, PublicContentRenderer $renderer): View
    {
        $post = Post::query()->where('status', 'published')->where('path', '/blog/'.$slug)->first();
        if ($post) {
            return $renderer->render($post);
        }

        $posts = [
            'turn-intent-into-pipeline-seo-led-content' => [
                'title' => 'How to turn intent into pipeline with SEO-led content',
                'category' => 'SEO strategy',
                'date' => 'October 2026',
                'content' => '<p>High-intent traffic only becomes valuable when your content system is aligned to commercial outcomes.</p><p>Start by mapping keyword intent to the buying journey, then build topic clusters that guide users toward a conversion action. Every article should answer the question behind the click while giving your team a clear next step.</p><p>When SEO is connected to lead capture, landing page design, and reporting, content becomes a repeatable growth channel rather than a disconnected marketing activity.</p>',
            ],
            'paid-media-dashboard-growth-team-track' => [
                'title' => 'The paid media dashboard every growth team should track',
                'category' => 'Marketing ops',
                'date' => 'September 2026',
                'content' => '<p>Paid media performance should be judged by pipeline quality, not just clicks and impressions.</p><p>Review spend efficiency, conversion rate by asset, and lead quality by source at the same time. A good dashboard keeps your team focused on what is driving revenue and what needs immediate adjustment.</p><p>A healthy channel review highlights both momentum and friction points in a format your broader team can understand and act on quickly.</p>',
            ],
            'what-good-conversion-design-actually-looks-like' => [
                'title' => 'What good conversion design actually looks like',
                'category' => 'CRO',
                'date' => 'August 2026',
                'content' => '<p>Good conversion design is often simple, clear, and tuned to decision-making moments.</p><p>It eliminates friction between interest and action. It reduces cognitive load, highlights trust signals, and makes the next step obvious. The result is not just more clicks, but more confident action.</p><p>Strong design is a commercial asset because it improves the quality of the journey users take once they are already interested in what you offer.</p>',
            ],
        ];

        $article = $posts[$slug] ?? null;

        abort_if(! $article, 404);

        return view('pages.blog-post', ['article' => $article]);
    }
}
