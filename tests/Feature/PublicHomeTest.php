<?php

use App\Models\Page;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the public home page', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Digital marketing that drives revenue.');
});

it('renders the primary public pages', function () {
    $this->get('/about-us')
        ->assertOk()
        ->assertSee('<h1>About Us</h1>', false)
        ->assertSee('Digital marketing solutions designed to generate revenue for your business')
        ->assertSee('Our values')
        ->assertSee('Transparency')
        ->assertSee('What our happy customers are saying')
        ->assertSee('View all reviews')
        ->assertSee('href="https://share.google/V9Qv6dImscMPQSWGX"', false)
        ->assertSee('<ul class="about-page__client-track" aria-label="Client logos">', false)
        ->assertSee('Clients We work with');
    $this->get('/services')->assertOk()->assertSee('Our services');
    $this->get('/contact')->assertOk()->assertSee('Contact');
    $this->get('/meet-the-team')->assertOk()->assertSee('<h1>Meet the Team</h1>', false);
    $this->get('/career')->assertOk()->assertSee('<h1>Career – Explore Opportunities</h1>', false);
    $this->get('/thank-you')->assertOk()->assertSee('Thanks for reaching out.');
});

it('uses the about layout for published imported pages and preserves their SEO metadata', function () {
    Page::query()->create([
        'title' => 'About Us',
        'path' => '/about-us',
        'status' => 'published',
        'published_at' => now(),
        'seo_title' => 'Imported about SEO title',
        'seo_description' => 'Imported about description.',
        'canonical_url' => 'https://trivo.in/about-us',
        'robots' => 'index,follow',
    ]);

    $this->get('/about-us')
        ->assertOk()
        ->assertSee('<title>Imported about SEO title</title>', false)
        ->assertSee('<meta name="description" content="Imported about description.">', false)
        ->assertSee('<link rel="canonical" href="https://trivo.in/about-us">', false)
        ->assertSee('What our happy customers are saying')
        ->assertSee('View all reviews');
});

it('renders the portfolio layout with original project URLs and imported SEO metadata', function () {
    $this->seed(\Database\Seeders\PortfolioProfilesSeeder::class);
    Page::query()->create([
        'title' => 'Portfolio',
        'path' => '/portfolio',
        'status' => 'published',
        'published_at' => now(),
        'seo_title' => 'Imported portfolio SEO title',
        'seo_description' => 'Imported portfolio description.',
        'canonical_url' => 'https://trivo.in/portfolio',
        'robots' => 'index,follow',
    ]);

    $response = $this->get('/portfolio');

    $response->assertOk()
        ->assertSee('<h1>Portfolio</h1>', false)
        ->assertSee('<title>Imported portfolio SEO title</title>', false)
        ->assertSee('<meta name="description" content="Imported portfolio description.">', false)
        ->assertSee('<link rel="canonical" href="https://trivo.in/portfolio">', false)
        ->assertSee('Luhaif Perfumes')
        ->assertSee('Miftahi Shop')
        ->assertSee('href="http://localhost:8000/luhaif-perfumes"', false)
        ->assertSee('href="http://localhost:8000/miftahi-shop"', false)
        ->assertSee('What Our Happy Customers Are Saying')
        ->assertSee('View all reviews')
        ->assertSee('class="about-page__client-track" aria-label="Client logos"', false);

    expect(substr_count($response->getContent(), '<article class="portfolio-project">'))->toBe(14);
});

it('renders the contact layout and preserves imported SEO metadata', function () {
    Page::query()->create([
        'title' => 'Contact',
        'path' => '/contact',
        'status' => 'published',
        'published_at' => now(),
        'seo_title' => 'Imported contact SEO title',
        'seo_description' => 'Imported contact description.',
        'canonical_url' => 'https://trivo.in/contact',
        'robots' => 'index,follow',
    ]);

    $this->get('/contact')
        ->assertOk()
        ->assertSee('<h1>Contact</h1>', false)
        ->assertSee('<title>Imported contact SEO title</title>', false)
        ->assertSee('<meta name="description" content="Imported contact description.">', false)
        ->assertSee('<link rel="canonical" href="https://trivo.in/contact">', false)
        ->assertSee('We’re here to help you grow')
        ->assertSee('name="message"', false)
        ->assertSee('mailto:contact@trivo.in')
        ->assertSee('View on map')
        ->assertSee('Rd No 1725, Riffa, Bahrain')
        ->assertSee('Jogeshwari West, Mumbai')
        ->assertSee('Surat, Gujarat')
        ->assertSee('class="home-footer-phone"', false);
});

it('renders the meet-the-team layout and preserves imported SEO metadata', function () {
    Page::query()->create([
        'title' => 'Meet the Team',
        'path' => '/meet-the-team',
        'status' => 'published',
        'published_at' => now(),
        'seo_title' => 'Imported team SEO title',
        'seo_description' => 'Imported team description.',
        'canonical_url' => 'https://trivo.in/meet-the-team',
        'robots' => 'index,follow',
    ]);

    $this->get('/meet-the-team')
        ->assertOk()
        ->assertSee('<h1>Meet the Team</h1>', false)
        ->assertSee('<title>Imported team SEO title</title>', false)
        ->assertSee('<meta name="description" content="Imported team description.">', false)
        ->assertSee('<link rel="canonical" href="https://trivo.in/meet-the-team">', false)
        ->assertSee('Meet the Trivo revenue revolutionaries')
        ->assertSee('We believe in making the world a better place')
        ->assertSee('Transparency')
        ->assertSee('Accountability')
        ->assertSee('Lifelong Learning')
        ->assertSee('Ambition')
        ->assertSee('Our mission')
        ->assertSee('Explore opportunities with us today')
        ->assertSee('Join our team')
        ->assertSee('<ul class="team-page__gallery-track">', false)
        ->assertSee('<ul class="about-page__client-track" aria-label="Client logos">', false)
        ->assertSee('class="home-footer-phone"', false);
});

it('renders the awards layout and preserves imported SEO metadata', function () {
    Page::query()->create([
        'title' => 'Awards',
        'path' => '/awards',
        'status' => 'published',
        'published_at' => now(),
        'seo_title' => 'Imported awards SEO title',
        'seo_description' => 'Imported awards description.',
        'canonical_url' => 'https://trivo.in/awards',
        'robots' => 'index,follow',
    ]);

    $response = $this->get('/awards');

    $response->assertOk()
        ->assertSee('<h1>Awards</h1>', false)
        ->assertSee('<title>Imported awards SEO title</title>', false)
        ->assertSee('<meta name="description" content="Imported awards description.">', false)
        ->assertSee('<link rel="canonical" href="https://trivo.in/awards">', false)
        ->assertSee('Award-winning digital marketing')
        ->assertSee('Top 50 Tech Companies')
        ->assertSee('India 5000 Best MSME Awards')
        ->assertSee('Top 20 Travel Tech Solution Provider')
        ->assertSee('UpCity Best of Marketing & Advertising')
        ->assertSee('Clutch Top Marketing Agency')
        ->assertSee('UpCity Top Ad Agency')
        ->assertSee('Start driving SEO results with Trivo')
        ->assertSee('href="http://localhost:8000/free-audit"', false)
        ->assertSee('<ul class="about-page__client-track" aria-label="Client logos">', false)
        ->assertSee('class="home-footer-phone"', false);

    expect(substr_count($response->getContent(), '<li class="awards-page__item">'))->toBe(6);
});

it('renders the career layout and preserves imported SEO metadata', function () {
    Page::query()->create([
        'title' => 'Career',
        'path' => '/career',
        'status' => 'published',
        'published_at' => now(),
        'seo_title' => 'Imported career SEO title',
        'seo_description' => 'Imported career description.',
        'canonical_url' => 'https://trivo.in/career',
        'robots' => 'index,follow',
    ]);

    $response = $this->get('/career');

    $response->assertOk()
        ->assertSee('<h1>Career – Explore Opportunities</h1>', false)
        ->assertSee('<title>Imported career SEO title</title>', false)
        ->assertSee('<meta name="description" content="Imported career description.">', false)
        ->assertSee('<link rel="canonical" href="https://trivo.in/career">', false)
        ->assertSee('Join the Dynamic IT and Marketing Team!')
        ->assertSee('Why Join Trivo?')
        ->assertSee('Supportive Team')
        ->assertSee('Innovative Marketing Perspectives')
        ->assertSee('Competitive Salary')
        ->assertSee('Career Development Opportunity')
        ->assertSee('Flexible Working Hours')
        ->assertSee('View the Open Position')
        ->assertSee('Digital Marketing Internship')
        ->assertSee('SEO Internship')
        ->assertSee('Social Media Manager Internship')
        ->assertSee('Our Culture')
        ->assertSee('HR Initiatives')
        ->assertSee('Know More About Us')
        ->assertSee('Apply Now')
        ->assertSee('src="https://img.youtube.com/vi/YiWWP5iIyPU/hqdefault.jpg"', false)
        ->assertSee('aria-label="Play Trivo digital marketing video"', false)
        ->assertSee('data-career-application', false)
        ->assertSee('mailto:letstalk@trivo.in', false)
        ->assertSee('class="home-footer-phone"', false);

    $html = $response->getContent();

    expect(substr_count($html, '<h1'))->toBe(1)
        ->and(substr_count($html, '<details'))->toBe(5)
        ->and(substr_count($html, 'ChatGPT-Image-May-9-2026-at-'))->toBe(5);
});

it('renders the blog cards with progressive loading, client logos, and imported SEO metadata', function () {
    Page::query()->create([
        'title' => 'Blog',
        'path' => '/blog',
        'status' => 'published',
        'published_at' => now(),
        'seo_title' => 'Imported blog SEO title',
        'seo_description' => 'Imported blog description.',
        'canonical_url' => 'https://trivo.in/blog',
        'robots' => 'index,follow',
    ]);

    for ($index = 1; $index <= 8; $index++) {
        Post::query()->create([
            'title' => 'Blog entry '.$index,
            'slug' => 'blog-entry-'.$index,
            'path' => '/blog/blog-entry-'.$index,
            'excerpt' => 'A concise article summary for blog entry '.$index.'.',
            'body' => '<p>Blog article content.</p>',
            'status' => 'published',
            'published_at' => now()->subDays($index),
        ]);
    }

    $response = $this->get('/blog');
    $response->assertOk()
        ->assertSee('<h1>Blog</h1>', false)
        ->assertSee('<title>Imported blog SEO title</title>', false)
        ->assertSee('<meta name="description" content="Imported blog description.">', false)
        ->assertSee('<link rel="canonical" href="https://trivo.in/blog">', false)
        ->assertSee('Blog entry 1')
        ->assertSee('Read More')
        ->assertSee('Load More')
        ->assertSee('Clients We work with')
        ->assertSee('<ul class="about-page__client-track" aria-label="Client logos">', false);

    $html = $response->getContent();
    expect(substr_count($html, 'class="blog-page__card"'))->toBe(8)
        ->and(preg_match_all('/<article class="blog-page__card" data-blog-card\s+hidden\s*>/', $html))->toBe(2);
});

it('renders the reference refund policy with imported SEO and marketing footer', function () {
    Page::query()->create([
        'title' => 'Return & Refund Policy',
        'path' => '/return-refund-policy',
        'status' => 'published',
        'published_at' => now(),
        'seo_title' => 'Imported refund policy SEO title',
        'seo_description' => 'Imported refund policy description.',
        'canonical_url' => 'https://trivo.in/return-refund-policy',
        'robots' => 'index,follow',
    ]);

    $response = $this->get('/return-refund-policy');
    $response->assertOk()
        ->assertSee('<h1>Return &amp; Refund Policy</h1>', false)
        ->assertSee('<title>Imported refund policy SEO title</title>', false)
        ->assertSee('<meta name="description" content="Imported refund policy description.">', false)
        ->assertSee('<link rel="canonical" href="https://trivo.in/return-refund-policy">', false)
        ->assertSee('All projects begin with the complete discovery/analysis')
        ->assertSee('SEO and Digital Marketing Packages are non-refundable')
        ->assertSee('We do not offer deposits or refunds')
        ->assertSee('class="home-enquiry home-enquiry--contact"', false)
        ->assertSee('class="return-refund-policy"', false);
});
