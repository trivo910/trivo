<?php

use App\Models\Media;
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
    $media = Media::query()->create([
        'legacy_wp_id' => 700004,
        'disk' => 'public',
        'path' => 'wp-content/uploads/article-hero.jpg',
        'original_name' => 'article-hero.jpg',
        'mime' => 'image/jpeg',
        'size' => 1024,
        'width' => 1200,
        'height' => 675,
        'alt' => 'Article hero image',
        'sha256' => hash('sha256', 'article-hero'),
    ]);

    Post::query()->create([
        'legacy_wp_id' => 700002,
        'title' => 'Imported article',
        'slug' => 'imported-article',
        'path' => '/imported-article',
        'body' => '<h2>Imported article feature title</h2><p>Article body from the source.</p><p><img src="https://trivo.in/wp-content/uploads/article-inline.jpg" alt="Inline article image"></p><h2>FAQ\'s</h2><summary>What is the answer?</summary><p>FAQ answer from the source.</p><script>alert("bad")</script>',
        'featured_media_id' => $media->id,
        'status' => 'published',
        'published_at' => '2025-01-02 03:04:05',
        'seo_title' => 'Imported article SEO title',
        'seo_description' => 'Imported article SEO description.',
        'canonical_url' => 'https://trivo.in/imported-article',
        'robots' => 'index,follow',
    ]);

    $response = $this->get('/imported-article');
    $response
        ->assertOk()
        ->assertSee('<title>Imported article SEO title</title>', false)
        ->assertSee('<link rel="canonical" href="https://trivo.in/imported-article">', false)
        ->assertSee('blog-post-page')
        ->assertSee('<h1>Imported article</h1>', false)
        ->assertSee('Imported article feature title')
        ->assertSee('src="http://localhost:8000/wp-content/uploads/article-hero.jpg"', false)
        ->assertSee('<p>Article body from the source.</p>', false)
        ->assertSee('src="/wp-content/uploads/article-inline.jpg"', false)
        ->assertSee('class="blog-post__faq-item"', false)
        ->assertSee('FAQ answer from the source.')
        ->assertDontSee('alert("bad")');
    expect(preg_match_all('/<h1\b/i', $response->getContent()))->toBe(1);
});

it('uses the same article layout for posts under the blog route', function () {
    Post::query()->create([
        'legacy_wp_id' => 700005,
        'title' => 'Blog route article',
        'slug' => 'blog-route-article',
        'path' => '/blog/blog-route-article',
        'body' => '<p>Blog route article content.</p><p><img src="https://trivo.in/wp-content/uploads/blog-route-hero.jpg" alt="Blog route hero"></p>',
        'status' => 'published',
        'published_at' => '2025-01-02 03:04:05',
    ]);

    $response = $this->get('/blog/blog-route-article');
    $response
        ->assertOk()
        ->assertSee('blog-post-page')
        ->assertSee('<h1>Blog route article</h1>', false)
        ->assertSee('Blog route article content.')
        ->assertSee('class="blog-post__hero-image"', false)
        ->assertSee('src="/wp-content/uploads/blog-route-hero.jpg"', false);
    expect(substr_count($response->getContent(), '/wp-content/uploads/blog-route-hero.jpg'))->toBe(1);
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
