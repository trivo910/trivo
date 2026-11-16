<?php

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the Nagpur performance marketing reference layout with imported content and responsive enquiry forms', function () {
    $html = <<<'HTML'
<h1>Best Performance Marketing Agency in Nagpur</h1>
<h2>Scale Smarter with Trivo</h2>
<p>Trivo helps businesses grow with measurable, data-backed campaigns.</p>
<p>Our performance marketing strategy focuses on quality leads and conversions.</p>
<h2>What is Performance Marketing?</h2>
<p>Performance marketing focuses on measurable outcomes.</p>
<ul><li>Google Search Ads (PPC)</li><li>Social Media Ads</li></ul>
<img src="/wp-content/uploads/2026/03/performance-marketing-abstract-concept-vector-illustration_107173-20393.avif" alt="">
<h3>Client Reviews</h3>
<h2>Why Trivo is the Right Performance Marketing Partner in Nagpur</h2>
<p>We understand local market needs and digital competition.</p>
<h2>Here’s why hundreds of businesses choose us</h2>
<h2><b>1. Local Expertise, National Reach</b></h2>
<p>Local knowledge helps make campaigns more relevant.</p>
<h2><b>2. Data-Driven Decisions</b></h2>
<p>Every decision is backed by campaign data.</p>
<h2><b>3. Transparent Reporting &amp; ROI Focus</b></h2>
<p>Clients receive performance reports and clear results.</p>
<h2><b>4. Custom Strategies for Every Business</b></h2>
<p>We tailor strategy to each business and audience.</p>
<h2><b>5. Full-Funnel Digital Execution</b></h2>
<p>We manage each stage of the sales funnel.</p>
<ul><li>Paid Search</li><li>Social Ads</li></ul>
<h2>Our Performance Marketing Services</h2>
<p>End-to-end performance marketing services for Nagpur businesses.</p>
<h2><b>1. Google Ads (PPC Campaigns)</b></h2>
<p>Reach high-intent customers searching on Google.</p>
<ul><li>Search ads</li><li>Display ads</li></ul>
<img src="/wp-content/uploads/2026/03/13744794_Mar-Business_2-scaled.jpg" alt="">
<img src="/wp-content/uploads/2026/03/12953772_Jan-Business_report_1-scaled.jpg" alt="">
<h2><b>3. Conversion Rate Optimization (CRO)</b></h2>
<p>Improve the number of visitors who take action.</p>
<img src="/wp-content/uploads/2026/03/remarketing-flat-modern-design-illustration_566886-530.jpg" alt="">
<h2><b>4. Remarketing Campaigns</b></h2>
<p>Bring interested visitors back with tailored ads.</p>
<img src="/wp-content/uploads/2026/03/vector-illustration-promotion-post-social-networks_292610-3-1024x1024.jpg" alt="">
<h4><b>5. Affiliate &amp; Influencer Marketing</b></h4>
<p>Partner with creators to reach new audiences.</p>
<h4>Industries We Serve in Nagpur &amp; Central India</h4>
<p>Tailored performance strategies for different business goals.</p>
<figure><img src="/wp-content/uploads/2026/03/Real-Estate-builders.png" alt=""></figure>
<h6>Real Estate &amp; Builders</h6>
<figure><img src="/wp-content/uploads/2026/03/eCommerce-Retail.png" alt=""></figure>
<h6>eCommerce &amp; Retail</h6>
<figure><img src="/wp-content/uploads/2026/03/Education-EdTech.png" alt=""></figure>
<h6>Education &amp; EdTech</h6>
<figure><img src="/wp-content/uploads/2026/03/Healthcare-Clinics.png" alt=""></figure>
<h6>Healthcare &amp; Clinics</h6>
<figure><img src="/wp-content/uploads/2026/03/Travel-Tourism.png" alt=""></figure>
<h6>Travel &amp; Tourism</h6>
<figure><img src="/wp-content/uploads/2026/03/SaaS-IT-Services.png" alt=""></figure>
<h6>SaaS &amp; IT Services</h6>
<figure><img src="/wp-content/uploads/2026/03/Fitness-Gyms.png" alt=""></figure>
<h6>Fitness &amp; Gyms</h6>
<figure><img src="/wp-content/uploads/2026/03/Restaurants-Cloud-Kitchens.png" alt=""></figure>
<h6>Restaurants &amp; Cloud Kitchens</h6>
<p>No matter your niche, our experts tailor campaigns to your goals.</p>
<h2>Our Performance Process</h2>
<ol><li><b>Goal Setting</b> – Define KPIs.</li><li><b>Audience Research</b> – Understand customer intent.</li></ol>
<h2>Tools &amp; Platforms We Use</h2>
<figure><img src="/wp-content/uploads/2026/03/Google-Ads-Manager.png" alt=""></figure>
<h5>Google Ads Manager</h5>
<figure><img src="/wp-content/uploads/2026/03/Meta-Business-Suite.png" alt=""></figure>
<h5>Meta Business Suite</h5>
<figure><img src="/wp-content/uploads/2026/03/SEMRush-Ahrefs.png" alt=""></figure>
<h5>Semrush, Ahrefs</h5>
<figure><img src="/wp-content/uploads/2026/03/Zapier-CRM-Integrations.png" alt=""></figure>
<h5>Zapier &amp; CRM Integrations</h5>
<figure><img src="/wp-content/uploads/2026/03/HubSpot-Mailchimp.png" alt=""></figure>
<h5>HubSpot, Mailchimp</h5>
<figure><img src="/wp-content/uploads/2026/03/Hotjar-VWO.png" alt=""></figure>
<h5>Hotjar, VWO</h5>
<figure><img src="/wp-content/uploads/2026/03/Google-Tag-Manager-Analytics-4.png" alt=""></figure>
<h5>Google Tag Manager &amp; Analytics 4</h5>
<h2>Ready to Grow with a Result-Driven Performance Marketing Agency in Nagpur?</h2>
<p>Let us show you what performance marketing can do for your brand.</p>
<h2>Frequently Asked Questions (FAQs)</h2>
<summary>Q1. What makes Trivo the best performance marketing agency in Nagpur?</summary>
<p>We combine local knowledge with measurable digital marketing expertise.</p>
<h3>Clients We works with</h3>
<figure><img src="/wp-content/uploads/2026/03/Sanjari-2.png" alt="Sanjari"></figure>
<figure><img src="/wp-content/uploads/2026/03/Polla-Tech.png" alt="Polla Tech"></figure>
<figure><img src="/wp-content/uploads/2026/03/Al-Bushra.png" alt="Al Bushra"></figure>
<figure><img src="/wp-content/uploads/2026/03/Mumbai-Darshan.png" alt="Mumbai Darshan"></figure>
<h3>Get in Touch</h3>
<p>Phone number:</p>
<a href="tel:+918806409826">+91 88064 09826</a>
<p>Email address:</p>
<a href="mailto:contact@trivo.in">CONTACT@TRIVO.IN</a>
<a href="https://maps.app.goo.gl/F7bhGNiJ9ZF17nDk9">VIEW ON MAP</a>
HTML;

    Page::query()->create([
        'title' => 'Best Performance Marketing Agency in Nagpur',
        'path' => '/performance-marketing-agency-nagpur',
        'status' => 'published',
        'published_at' => now(),
        'blocks' => [[
            'type' => 'legacy_html',
            'html' => $html,
        ]],
    ]);

    $response = $this->get('/performance-marketing-agency-nagpur');
    $response->assertOk()
        ->assertSee('<title>Best Performance Marketing Agency in Nagpur | Trivo</title>', false)
        ->assertSee('href="http://localhost:8000/performance-marketing-agency-nagpur"', false)
        ->assertSee('service-page--performance-marketing-agency-nagpur', false)
        ->assertSee('performance-marketing-proof', false)
        ->assertSee('performance-marketing-benefit', false)
        ->assertSee('performance-marketing-industry', false)
        ->assertSee('performance-marketing-tool', false)
        ->assertSee('service-section--performance-hero', false)
        ->assertSee('service-section--performance-service', false)
        ->assertSee('Clients We works with')
        ->assertSee('Get in Touch')
        ->assertSee('href="mailto:contact@trivo.in"', false)
        ->assertSee('data-backed campaigns')
        ->assertDontSee('href="/?name=', false);

    $rendered = $response->getContent();
    expect(substr_count($rendered, '<h1'))->toBe(1)
        ->and(substr_count($rendered, 'class="performance-marketing-benefit"'))->toBe(5)
        ->and(substr_count($rendered, 'class="performance-marketing-industry"'))->toBe(8)
        ->and(substr_count($rendered, 'class="performance-marketing-tool"'))->toBe(7)
        ->and(substr_count($rendered, 'class="performance-marketing-form"'))->toBe(2)
        ->and(substr_count($rendered, 'data-home-enquiry'))->toBe(3)
        ->and(strpos($rendered, 'performance-marketing-proof'))->toBeLessThan(strpos($rendered, 'Why Trivo is the Right Performance Marketing Partner'))
        ->and(strpos($rendered, 'performance-marketing-industries'))->toBeLessThan(strpos($rendered, 'Our Performance Process'));
});
