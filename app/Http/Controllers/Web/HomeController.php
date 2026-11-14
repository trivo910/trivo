<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Post;
use App\Services\Content\PublicContentRenderer;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(PublicContentRenderer $renderer): View
    {
        return $this->page('/', 'pages.home', $renderer);
    }

    public function about(PublicContentRenderer $renderer): View
    {
        return $this->page('/about-us', 'pages.about', $renderer);
    }

    public function services(PublicContentRenderer $renderer): View
    {
        return $this->page('/services', 'pages.services', $renderer);
    }

    public function meetTheTeam(PublicContentRenderer $renderer): View
    {
        return $this->page('/meet-the-team', 'pages.meet-the-team', $renderer);
    }

    public function awards(PublicContentRenderer $renderer): View
    {
        return $this->page('/awards', 'pages.awards', $renderer);
    }

    public function career(PublicContentRenderer $renderer): View
    {
        return $this->page('/career', 'pages.career', $renderer);
    }

    public function contact(PublicContentRenderer $renderer): View
    {
        return $this->page('/contact', 'pages.contact', $renderer);
    }

    public function thankYou(): View
    {
        return view('pages.thank-you');
    }

    public function privacyPolicy(PublicContentRenderer $renderer): View
    {
        return $this->page('/privacy-policy', 'pages.privacy-policy', $renderer);
    }

    public function termsConditions(PublicContentRenderer $renderer): View
    {
        return $this->page('/terms-conditions', 'pages.terms-conditions', $renderer);
    }

    public function refundPolicy(PublicContentRenderer $renderer): View
    {
        return $this->page('/return-refund-policy', 'pages.return-refund-policy', $renderer);
    }

    public function portfolio(PublicContentRenderer $renderer): View
    {
        return $this->page('/portfolio', 'pages.portfolio', $renderer);
    }

    public function portfolioItem(string $slug, PublicContentRenderer $renderer): View
    {
        $post = Post::query()->where('status', 'published')->where('path', '/'.$slug)->first();
        if ($post) {
            return $renderer->render($post);
        }

        $items = [
            'travel-brand-growth-sprint' => [
                'title' => 'Travel brand growth sprint',
                'summary' => 'SEO, paid media, and landing page optimisation for a fast-moving travel brand.',
                'content' => '<p>We built a measurable growth plan that increased qualified inquiries and improved conversion quality across both search and paid channels.</p><p>By tightening messaging, improving landing pages, and aligning campaigns to customer intent, the brand achieved stronger efficiency without sacrificing volume.</p>',
            ],
            'b2b-pipeline-acceleration' => [
                'title' => 'B2B pipeline acceleration',
                'summary' => 'A demand generation engine that translated intent into consistent lead flow.',
                'content' => '<p>Our team refined the funnel and campaign targeting across multiple high-intent channels, creating a more predictable pipeline for the sales team.</p><p>The strategy focused on cleaner segmentation and better qualification so marketing spend generated stronger commercial outcomes.</p>',
            ],
        ];

        abort_if(! isset($items[$slug]), 404);

        return view('pages.portfolio-item', ['item' => $items[$slug]]);
    }

    private function page(string $path, string $fallback, PublicContentRenderer $renderer): View
    {
        $page = Page::query()->where('status', 'published')->where('path', $path)->first();

        return $page ? $renderer->render($page) : view($fallback);
    }
}
