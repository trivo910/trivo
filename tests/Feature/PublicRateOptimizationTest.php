<?php

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the conversion rate optimization layout and excludes unrelated paid search content', function () {
    $html = <<<'HTML'
<h1>Conversion Rate Optimization</h1>
<h2>Where creativity meets strategy</h2>
<p>We create digital experiences that help businesses grow.</p>
<a href="/portfolio/">View Our Portfolio</a>
<img src="/wp-content/uploads/2026/01/conversion-rate-optimization-concept-illustration_114360-7513-removebg-preview.png" alt="">
<h3>UI/UX Design</h3>
<p>Designing websites, apps, and software.</p>
<p><a href="/wp-content/uploads/2023/06/Stats.svg"><img src="/wp-content/uploads/2025/03/4336501-scaled.jpg" alt=""></a></p>
<h3>Web Development</h3>
<img src="/wp-content/uploads/2026/01/10276838_4380747.jpg" alt="">
<h3>3D Designs</h3>
<img src="/wp-content/uploads/2026/01/4334861_2221158.jpg" alt="">
<h3>Digital Marketing Design</h3>
<img src="/wp-content/uploads/2026/01/24750021_6934324.jpg" alt="">
<h3>Motion Graphics</h3>
<p>Animated graphic design for digital content.</p>
<img src="/wp-content/uploads/2026/01/motion-graphics.jpg" alt="">
<img src="/wp-content/uploads/2026/03/Our-design-masterpieces.png" alt="">
<h4>Combining search engine marketing with conversion rate optimization (CRO)</h4>
<p>Improve the number of people who contact you after they click on an ad.</p>
<h3>Real certified Google Ads professionals</h3>
<p>Our certified specialists help you achieve your goals.</p>
<img src="/wp-content/uploads/2023/05/google-experts-1024x750.jpg" alt="">
<h6>what you get</h6>
<h2>What is included in our paid search marketing services?</h2>
<p>Unrelated paid search content.</p>
HTML;

    Page::query()->create([
        'title' => 'Conversion Rate Optimization',
        'path' => '/rate-optimization',
        'status' => 'published',
        'published_at' => now(),
        'blocks' => [[
            'type' => 'legacy_html',
            'html' => $html,
        ]],
    ]);

    $response = $this->get('/rate-optimization');
    $response->assertOk()
        ->assertSee('<title>Conversion Rate Optimization | Trivo</title>', false)
        ->assertSee('href="http://localhost:8000/rate-optimization"', false)
        ->assertSee('service-page--rate-optimization', false)
        ->assertSee('service-section--cro-explanation', false)
        ->assertSee('service-section--certified', false)
        ->assertSee('href="/portfolio/"', false)
        ->assertSee('Our-design-masterpieces.png')
        ->assertSee('google-experts-1024x750.jpg')
        ->assertDontSee('What is included in our paid search marketing services?');

    $rendered = $response->getContent();
    expect(substr_count($rendered, '<h1'))->toBe(1)
        ->and(substr_count($rendered, 'role="tab"'))->toBe(5)
        ->and(substr_count($rendered, 'role="tabpanel"'))->toBe(5)
        ->and(strpos($rendered, 'UI/UX Design'))->toBeLessThan(strpos($rendered, 'Combining search engine marketing'))
        ->and(strpos($rendered, 'Combining search engine marketing'))->toBeLessThan(strpos($rendered, 'Real certified Google Ads professionals'));
});
