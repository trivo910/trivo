<?php

use App\Models\Page;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the graphic design sections, banner and published testimonials', function () {
    $html = <<<'HTML'
<h1>Graphic Design</h1>
<h2>Designs Without Limits</h2>
<p>Designs that help your brand stand out.</p>
<a href="/contact">LET’S TALK</a>
<a href="/contact">Connect our experts</a>
<img src="/wp-content/uploads/2023/06/GettyImages-1404633864-Converted.png" alt="">
<img src="/wp-content/uploads/2023/05/GettyImages-1341057712.jpg" alt="">
<img src="/wp-content/uploads/2023/05/pexels-wendy-wei-1656684-300x300.jpg" alt="">
<img src="/wp-content/uploads/2023/05/pexels-moose-photos-1036622-300x300.jpg" alt="">
<img src="/wp-content/uploads/2023/05/pexels-cottonbro-studio-5083220-300x300.jpg" alt="">
<h4>2,120,240,368</h4>
<p>Revenue driven<br>for our clients</p>
<ul><li></li><li></li><li></li><li></li><li></li></ul>
<p>5000+ Client reviews</p>
<h2>we do</h2>
<h3>Professional Graphics Designing for Your Brand</h3>
<p>Business-focused graphic design.</p>
<a href="/about-us">More about us</a>
<h4>Creative Designs</h4><p>Distinctive creative work.</p>
<h4>High Quality</h4><p>Professional production.</p>
<h4>Fast Delivery</h4><p>Reliable delivery.</p>
<img src="/wp-content/uploads/2025/03/1280-x-520-px-01-1-scaled.jpg" alt="">
<h2>Our Expertise</h2>
<h3>Designs Backed by Insights</h3>
<a href="/marketing-solutions">View all solutions</a>
<h5>Brand Identity Design</h5><p>Brand identity copy.</p>
<h4>Motion Graphics</h4><p>Animation tab content.</p>
<h4>UI/UX Design</h4><p>Interface tab content.</p>
<h4>Marketing Design</h4><p>Marketing tab content.</p>
<h3>Why Choose Us?</h3>
<p>Creative design expertise.</p>
<a href="/contact">Get Proposal</a>
<h4>The proof is in the numbers</h4>
<h2>37%</h2><h2>100%</h2><h2>81%</h2>
<h3>282,000+</h3><p>Leads generated so far…</p>
<a href="/contact">Contact us</a>
<ul><li></li><li></li><li></li><li></li><li></li></ul>
<p>5000+ Client reviews</p>
<a href="https://share.google/V9Qv6dImscMPQSWGX">View all reviews</a>
<img src="/wp-content/uploads/2023/05/Line-arrow-2.svg" alt="">
<img src="/wp-content/uploads/2023/05/pexels-wendy-wei-1656684-300x300.jpg" alt="">
HTML;

    Page::query()->create([
        'title' => 'Graphic Design',
        'path' => '/graphic-design',
        'status' => 'published',
        'published_at' => now(),
        'blocks' => [[
            'type' => 'legacy_html',
            'html' => $html,
        ]],
    ]);

    Testimonial::query()->create([
        'title' => 'Client Example',
        'path' => null,
        'status' => 'published',
        'sort' => 1,
        'blocks' => [['type' => 'paragraph', 'text' => 'A published graphic design review.']],
        'details' => ['role' => 'Brand Director'],
    ]);
    Testimonial::query()->create([
        'title' => 'Draft Example',
        'path' => null,
        'status' => 'draft',
        'sort' => 2,
        'blocks' => [['type' => 'paragraph', 'text' => 'This draft review must stay private.']],
    ]);

    $response = $this->get('/graphic-design');
    $response->assertOk()
        ->assertSee('graphic-design-hero__collage', false)
        ->assertSee('Get Stunning Designs Today!')
        ->assertSee('Award Winning Agency')
        ->assertSee('Connect our experts')
        ->assertSee('2,120,240,368')
        ->assertSee('service-section--brand-intro', false)
        ->assertSee('service-section--why-choose', false)
        ->assertSee('service-design-banner', false)
        ->assertSee('Average increase in sales for our clients')
        ->assertSee('Google and Facebook-certified team')
        ->assertSee('Results improved compared to previous agencies')
        ->assertSee('graphic-design-reviews', false)
        ->assertSee('A published graphic design review.')
        ->assertSee('Client Example')
        ->assertSee('Brand Director')
        ->assertSee('View all reviews')
        ->assertDontSee('This draft review must stay private.');

    $html = $response->getContent();
    expect(substr_count($html, '<h1'))->toBe(1)
        ->and(strpos($html, 'Fast Delivery'))->toBeLessThan(strpos($html, 'service-design-banner'))
        ->and(strpos($html, 'service-design-banner'))->toBeLessThan(strpos($html, 'Our Expertise'));
});
