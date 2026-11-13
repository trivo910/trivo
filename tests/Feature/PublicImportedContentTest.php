<?php

use App\Models\Page;
use App\Models\Post;
use App\Services\Content\ContentDrafts;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders sanitized imported page HTML at its preserved path', function () {
    Page::query()->create([
        'legacy_wp_id' => 700001,
        'title' => 'Imported page',
        'path' => '/imported-page',
        'status' => 'published',
        'blocks' => [[
            'type' => 'legacy_html',
            'html' => '<h1>Imported heading</h1><p onclick="alert(1)">Visible text</p><h1>Secondary heading</h1><script>window.compromised = true</script><a href="javascript:alert(1)">Unsafe link</a><img src="https://trivo.in/wp-content/uploads/sample.jpg" alt="Sample image">',
        ]],
        'seo_title' => 'Imported & "verified" title',
        'seo_description' => 'Imported description with "quotes".',
        'canonical_url' => 'https://trivo.in/imported-page',
        'robots' => 'index,follow',
    ]);

    $response = $this->get('/imported-page');

    $response->assertOk()
        ->assertSee('<h1>Imported heading</h1>', false)
        ->assertSee('<h2>Secondary heading</h2>', false)
        ->assertSee('Visible text')
        ->assertSee('src="/wp-content/uploads/sample.jpg"', false)
        ->assertSee('Imported &amp; &quot;verified&quot; title')
        ->assertDontSee('window.compromised')
        ->assertDontSee('onclick=')
        ->assertDontSee('javascript:');
    expect(preg_match_all('/<h1\b/i', $response->getContent()))->toBe(1);
});

it('renders imported posts from their exact root-level path', function () {
    Post::query()->create([
        'legacy_wp_id' => 700002,
        'title' => 'Imported article',
        'slug' => 'imported-article',
        'path' => '/imported-article',
        'body' => '<p>Article body from the source.</p><script>alert("bad")</script>',
        'status' => 'published',
        'published_at' => '2025-01-02 03:04:05',
    ]);

    $this->get('/imported-article')
        ->assertOk()
        ->assertSee('<h1 class="mb-8', false)
        ->assertSee('Imported article')
        ->assertSee('<p>Article body from the source.</p>', false)
        ->assertDontSee('alert("bad")');
});

it('retains imported page HTML through the ordinary section editor validation', function () {
    $html = '<h1>Imported heading</h1><p>Imported body.</p>';
    $page = Page::query()->create([
        'legacy_wp_id' => 700003,
        'title' => 'Imported page',
        'path' => '/preserved-import',
        'status' => 'published',
        'blocks' => [['type' => 'legacy_html', 'html' => $html]],
    ]);

    $result = app(ContentDrafts::class)->validate([
        'title' => 'Updated title',
        'path' => '/preserved-import',
        'blocks' => [['type' => 'legacy_html', 'html' => $html]],
        'content_version' => 1,
    ], $page);

    expect($result['blocks'])->toBe([['type' => 'legacy_html', 'html' => $html]]);
});
