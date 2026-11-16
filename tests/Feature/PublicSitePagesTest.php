<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the core public and legal pages', function () {
    $this->get('/blog')
        ->assertOk()
        ->assertSee('<h1>Blog</h1>', false)
        ->assertSee('site-header__who-menu')
        ->assertSee('About Us')
        ->assertSee('Awards')
        ->assertSee('Meet the Team')
        ->assertSee('Career – Explore Opportunities')
        ->assertSee('site-header__mobile-who');
    $this->get('/blog/turn-intent-into-pipeline-seo-led-content')
        ->assertOk()
        ->assertSee('blog-post-page')
        ->assertSee('How to turn intent into pipeline with SEO-led content');
    $this->get('/privacy-policy')->assertOk()->assertSee('Privacy policy');
    $this->get('/terms-conditions')->assertOk()->assertSee('Terms')->assertSee('conditions');
    $this->get('/return-refund-policy')->assertOk()->assertSee('Return')->assertSee('refund');
});

it('shows a published post image on the blog listing when no media record is assigned', function () {
    Post::query()->create([
        'legacy_wp_id' => 700006,
        'title' => 'Blog listing image article',
        'slug' => 'blog-listing-image-article',
        'path' => '/blog/blog-listing-image-article',
        'body' => '<p><img src="https://trivo.in/wp-content/uploads/blog-listing.jpg" alt="Blog listing image"></p>',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get('/blog')
        ->assertOk()
        ->assertSee('class="blog-page__card-img"', false)
        ->assertSee('src="/wp-content/uploads/blog-listing.jpg"', false);
});
