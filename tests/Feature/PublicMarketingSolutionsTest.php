<?php

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the marketing solutions page as an alternating service layout', function () {
    Page::query()->create([
        'title' => 'Marketing Solutions - Trivo',
        'path' => '/marketing-solutions',
        'status' => 'published',
        'blocks' => [],
    ]);

    $response = $this->get('/marketing-solutions');

    $response->assertOk()
        ->assertSee('<h1 class="marketing-solutions-page__title">Our Marketing Solutions</h1>', false)
        ->assertSee('Paid Search Marketing')
        ->assertSee('Social Media Marketing')
        ->assertSee('Search Engine Optimization')
        ->assertSee('Google Shopping')
        ->assertSee('Email Marketing')
        ->assertSee('Performance Marketing')
        ->assertSee('Graphic Design')
        ->assertSee('Amazon Shopping')
        ->assertSee('class="site-header__solutions-menu" aria-label="Marketing solutions"', false)
        ->assertSee('/paid-search-marketing"', false)
        ->assertSee('/rate-optimization"', false)
        ->assertSee('class="site-header__mobile-solutions"', false)
        ->assertSee('Explore all<br>solutions', false)
        ->assertSee('Get In Touch')
        ->assertSee('/paid-search-marketing');

    preg_match_all('/<div class="marketing-solution__media">\s*<img\s+src="([^"]+)"/', $response->getContent(), $imageMatches);
    expect($imageMatches[1])->toHaveCount(8);

    foreach ($imageMatches[1] as $source) {
        $relativePath = ltrim((string) parse_url($source, PHP_URL_PATH), '/');
        expect(file_exists(public_path($relativePath)))->toBeTrue();
    }
});

it('renders SEO content in the dedicated service layout and preserves its SEO metadata', function () {
    Page::query()->create([
        'title' => 'Search engine optimization',
        'path' => '/search-engine-optimization',
        'status' => 'published',
        'published_at' => now(),
        'seo_title' => 'Imported SEO title',
        'seo_description' => 'Imported SEO description.',
        'canonical_url' => 'https://trivo.in/search-engine-optimization',
        'robots' => 'index,follow',
        'blocks' => [[
            'type' => 'legacy_html',
            'html' => '<h1>Search engine optimization</h1><h4>Dominate Search Rankings &amp; Drive Organic Growth</h4><p>Improve organic traffic and increase revenue.</p><h4>Why Invest in SEO?</h4><strong>Higher Search Rankings</strong><br><strong>Increased Website Traffic</strong><br><h3>Our Complete SEO Services</h3><h5>On-Page SEO</h5><h5>Off-Page SEO</h5><h5>Technical SEO</h5>',
        ]],
    ]);

    $response = $this->get('/search-engine-optimization');
    $response->assertOk()
        ->assertSee('<title>Imported SEO title</title>', false)
        ->assertSee('<meta name="description" content="Imported SEO description.">', false)
        ->assertSee('<link rel="canonical" href="https://trivo.in/search-engine-optimization">', false)
        ->assertSee('class="paid-search-page service-page service-page--search-engine-optimization "', false)
        ->assertSee('Dominate Search Rankings')
        ->assertSee('Why Invest in SEO?')
        ->assertSee('Our Complete SEO Services')
        ->assertSee('On-Page SEO')
        ->assertSee('We offer a full range of SEO services designed to improve your website’s visibility')
        ->assertSee('Keyword Research & Optimization')
        ->assertSee('SEO-Friendly Content Creation')
        ->assertSee('Meta Tags, Headers & URL Structuring')
        ->assertSee('Image Optimization & Internal Linking')
        ->assertSee('High-Quality Link Building')
        ->assertSee('Guest Blogging & Outreach')
        ->assertSee('Social Media & Brand Mentions')
        ->assertSee('Local SEO & Google My Business Optimization')
        ->assertSee('Website Speed & Mobile Optimization')
        ->assertSee('Fixing Broken Links & Redirects')
        ->assertSee('Schema Markup Implementation')
        ->assertSee('Secure HTTPS & Core Web Vitals Optimization')
        ->assertSee('class="service-feature-points"', false);

    expect(substr_count($response->getContent(), '<h1'))->toBe(1);
});
