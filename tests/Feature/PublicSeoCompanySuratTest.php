<?php

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the Surat SEO reference layout with audit forms and distinct service sections', function () {
    $html = <<<'HTML'
<h1>SEO Company in Surat</h1>
<h2>Boost Your Online Visibility with Trivo</h2>
<p>Grow your visibility with a strategy built for Surat businesses.</p>
<h2>Why Your Business in Surat Needs SEO</h2>
<p>Improve visibility and attract local customers.</p>
<img src="/wp-content/uploads/2026/03/seo-analytics-concept-illustration_86047-1034.avif" alt="">
<h3>Client Reviews</h3>
<h2>Why Choose Trivo as Your SEO Partner in Surat?</h2>
<h2>Here’s what makes Trivo a trusted SEO partner:</h2>
<h5>1. Data-Driven SEO Strategies</h5><p>We use meaningful performance data.</p>
<h5>2. Local SEO Expertise</h5><p>We understand local search.</p>
<h2>Our SEO Services in Surat</h2>
<h4>1. Keyword Research &amp; Strategy</h4><img src="/wp-content/uploads/2026/03/seo-analytics-concept-illustration_86047-1034.avif" alt="">
<h4>2. On-Page SEO</h4><img src="/wp-content/uploads/2026/03/seo-analytics-concept-illustration_86047-1034.avif" alt="">
<h4>3. Technical SEO</h4><img src="/wp-content/uploads/2026/03/seo-analytics-concept-illustration_86047-1034.avif" alt="">
<h4>4. Local SEO</h4><img src="/wp-content/uploads/2026/03/seo-analytics-concept-illustration_86047-1034.avif" alt="">
<h4>5. Link Building &amp; Off-Page SEO</h4><img src="/wp-content/uploads/2026/03/seo-analytics-concept-illustration_86047-1034.avif" alt="">
<h4>6. Content Creation &amp; Blogging</h4><img src="/wp-content/uploads/2026/03/seo-analytics-concept-illustration_86047-1034.avif" alt="">
<h2>Additional SEO Support Services</h2><ul><li>Website SEO Audits</li><li>Competitor Benchmarking</li></ul>
<h2>Our SEO Services in Surat</h2>
<h6>1. SEO Audit &amp; Analysis</h6><h6>2. Keyword Research</h6><h6>3. On-Page Optimization</h6>
<h2>Who We Work With</h2>
<p>Real Estate Developers</p><p>Local Service Providers</p><p>Educational Institutions</p>
<h2>Tools We Use for SEO Success</h2>
<figure><img src="/wp-content/uploads/2026/03/Google-Trends.png" alt="Google Trends"></figure>
<h2>Let’s Grow Your Rankings, Leads, and Sales—Together</h2>
<h2>Frequently Asked Questions (FAQs)</h2>
<summary>Q1. What makes Trivo a leading SEO company in Surat?</summary><p>We build strategies around your goals.</p>
<h2>Get Started with the Best SEO Company in Surat</h2>
<h2>Request a Free SEO Audit</h2>
<a href="tel:+918806409826">+91 88064 09826</a>
HTML;

    Page::query()->create([
        'title' => 'SEO Company in Surat',
        'path' => '/seo-company-surat',
        'status' => 'published',
        'published_at' => now(),
        'blocks' => [[
            'type' => 'legacy_html',
            'html' => $html,
        ]],
    ]);

    $response = $this->get('/seo-company-surat')->assertOk();
    $response->assertSee('service-section--surat-hero', false)
        ->assertSee('surat-seo-form-card', false)
        ->assertSee('surat-seo-reviews__metrics', false)
        ->assertSee('surat-seo-services__grid', false)
        ->assertSee('surat-seo-support', false)
        ->assertSee('surat-seo-audit-services', false)
        ->assertSee('surat-seo-industries', false)
        ->assertSee('service-section--surat-growth', false)
        ->assertSee('service-section--surat-faq', false)
        ->assertSee('service-section--surat-audit', false);

    $rendered = $response->getContent();
    expect(substr_count($rendered, 'class="surat-seo-service"'))->toBe(6)
        ->and(substr_count($rendered, 'class="surat-seo-form"'))->toBe(2)
        ->and(substr_count($rendered, 'class="surat-seo-reviews__metric"'))->toBe(3)
        ->and(substr_count($rendered, '<h1'))->toBe(1)
        ->and(strpos($rendered, 'surat-seo-services'))->toBeLessThan(strpos($rendered, 'surat-seo-support'));
});
