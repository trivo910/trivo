<?php

use App\Actions\Admin\ChangeBlogPostPublication;
use App\Actions\Admin\SaveAuthorProfile;
use App\Actions\Admin\SaveBlogPost;
use App\Actions\Admin\SaveBlogTerm;
use App\Models\Author;
use App\Models\Post;
use App\Models\Revision;
use App\Services\Content\BlogTerms;
use App\Services\Content\PublishDuePosts;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->seed(RolePermissionSeeder::class);
    $this->admin = adminAccount('Editor');
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
});

function postDraftFixture(array $values = []): array
{
    return array_replace(['title' => 'Synthetic post', 'slug' => 'synthetic-post', 'path' => '/synthetic-post/', 'excerpt' => 'Synthetic excerpt.', 'body' => "Synthetic post body.\nSecond paragraph.", 'tag_ids' => [], 'secondary_keywords' => [], 'is_cornerstone' => false, 'show_toc' => true], $values);
}
function activeTerm(string $kind, array $values = []): mixed
{
    return app(SaveBlogTerm::class)->handle(app(BlogTerms::class)->model($kind), array_replace([
        'name' => 'Synthetic '.$kind,
        'slug' => 'synthetic-'.$kind,
        'path' => '/'.$kind.'/Synthetic-term/',
        'description' => 'Synthetic term description.',
    ], $values));
}

it('creates a plain-text draft with active author category and tags', function () {
    $author = app(SaveAuthorProfile::class)->handle(new Author, ['name' => 'Synthetic author', 'path' => '/author/synthetic-author/', 'bio' => 'Synthetic biography.']);
    $category = activeTerm('categories');
    $tag = activeTerm('tags');
    $this->post(route('admin.posts.store'), postDraftFixture(['author_id' => $author->id, 'category_id' => $category->id, 'tag_ids' => [$tag->id], 'focus_keyword' => 'Synthetic', 'secondary_keywords' => ['draft', 'draft'], 'seo_title' => 'Synthetic SEO title', 'seo_description' => 'Synthetic search description.', 'is_cornerstone' => true, 'show_toc' => false, 'legacy_wp_id' => 9]))->assertSessionHasNoErrors();
    $post = Post::query()->sole();
    expect($post->status)->toBe('draft')->and($post->legacy_wp_id)->toBeNull()->and($post->content_version)->toBe(1)->and($post->tags()->pluck('tags.id')->all())->toBe([$tag->id])
        ->and($post->reading_time)->toBe(1)->and($post->focus_keyword)->toBe('Synthetic')->and($post->secondary_keywords)->toBe(['draft'])->and($post->seo_title)->toBe('Synthetic SEO title')->and($post->is_cornerstone)->toBeTrue()->and($post->show_toc)->toBeFalse()->and($post->seo_score)->toBeGreaterThan(0)->toBeLessThanOrEqual(100)->and($post->seo_analysis)->toHaveKey('seo_title')->and($post->seo_analysis)->toHaveKey('seo_title_length')->and($post->seo_analysis)->toHaveKey('density')->and(Revision::query()->where('revisionable_type', Post::class)->sole()->snapshot['kind'])->toBe('post_draft');
    $this->get(route('admin.posts.edit', $post))->assertOk()->assertSee('Saved content analysis')->assertSee('SEO score:')->assertSee('Post revision history')->assertSee('Restore this revision');
    $this->put(route('admin.posts.update', $post), postDraftFixture(['content_version' => 1, 'focus_keyword' => 'Synthetic', 'secondary_keywords_text' => "draft\nkeyword", 'seo_title' => 'Synthetic SEO title', 'seo_description' => 'Synthetic search description.']))->assertSessionHasNoErrors();
    expect($post->fresh()->secondary_keywords)->toBe(['draft', 'keyword']);
});

it('rejects unsafe paths and inactive assignment IDs', function (array $values, string $field) {
    $this->post(route('admin.posts.store'), postDraftFixture($values))->assertSessionHasErrors($field);
    expect(Post::query()->count())->toBe(0);
})->with([[['path' => '/wp-admin'], 'path'], [['author_id' => 99], 'author_id'], [['category_id' => 99], 'category_id'], [['tag_ids' => [99]], 'tag_ids']]);

it('keeps post paths fixed and rejects stale saves', function () {
    $post = app(SaveBlogPost::class)->handle(new Post, postDraftFixture());
    $this->put(route('admin.posts.update', $post), postDraftFixture(['title' => 'Changed post', 'content_version' => 1]))->assertSessionHasNoErrors();
    $this->put(route('admin.posts.update', $post), postDraftFixture(['content_version' => 1]))->assertSessionHasErrors('content_version');
    $this->put(route('admin.posts.update', $post), postDraftFixture(['path' => '/renamed/', 'content_version' => 2]))->assertSessionHasErrors('path');
    expect($post->fresh()->title)->toBe('Changed post')->and($post->fresh()->content_version)->toBe(2);
});

it('lists drafts with score filters', function () {
    $post = app(SaveBlogPost::class)->handle(new Post, postDraftFixture(['focus_keyword' => 'Synthetic']));
    Post::query()->create(['title' => 'Unscored draft', 'slug' => 'unscored', 'path' => '/unscored/', 'body' => 'Body', 'status' => 'draft']);
    $this->get(route('admin.posts.index'))->assertOk()->assertSee('SEO score')->assertSee($post->seo_score.'/100');
    $this->get(route('admin.posts.index', ['score' => 'needs_attention']))->assertOk()->assertSee('Unscored draft');
    $this->get(route('admin.posts.index', ['score' => 'invalid']))->assertSessionHasErrors('score');
});

it('restores only matching revisions and archives eligible drafts', function () {
    $post = app(SaveBlogPost::class)->handle(new Post, postDraftFixture());
    $revision = Revision::query()->where('revisionable_type', Post::class)->sole();
    app(SaveBlogPost::class)->handle($post, postDraftFixture(['title' => 'Changed post', 'content_version' => 1]));
    $this->post(route('admin.posts.revision', ['post' => $post, 'revision' => $revision]), ['content_version' => 2])->assertSessionHasNoErrors();
    expect($post->fresh()->title)->toBe('Synthetic post')->and($post->fresh()->content_version)->toBe(3);
    $wrong = Revision::query()->create(['revisionable_type' => Post::class, 'revisionable_id' => $post->id, 'snapshot' => ['kind' => 'seo', 'content' => []]]);
    $this->post(route('admin.posts.revision', ['post' => $post, 'revision' => $wrong]), ['content_version' => 3])->assertNotFound();
    $this->delete(route('admin.posts.archive', $post), ['content_version' => 99])->assertSessionHasErrors('content_version');
    $this->delete(route('admin.posts.archive', $post), ['content_version' => 3])->assertSessionHasNoErrors();
    $this->post(route('admin.posts.restore', $post), ['content_version' => 4])->assertSessionHasNoErrors();
    $post->update(['legacy_wp_id' => 1]);
    $this->delete(route('admin.posts.archive', $post), ['content_version' => 5])->assertSessionHasNoErrors();
    expect($post->fresh()->trashed())->toBeTrue();
});

it('publishes valid posts and rejects incomplete metadata', function () {
    $post = app(SaveBlogPost::class)->handle(new Post, postDraftFixture(['seo_title' => 'Synthetic SEO title', 'seo_description' => 'Synthetic search description.']));
    $this->post(route('admin.posts.publication', $post), ['operation' => 'publish', 'content_version' => 1])->assertSessionHasNoErrors();
    expect($post->fresh()->status)->toBe('published')->and($post->fresh()->published_at)->not->toBeNull()->and($post->fresh()->publication_locked_at)->not->toBeNull();
    $this->put(route('admin.posts.update', $post), postDraftFixture(['content_version' => 2, 'title' => 'Updated live article', 'seo_title' => 'Synthetic SEO title', 'seo_description' => 'Synthetic search description.']))->assertSessionHasNoErrors();
    expect($post->fresh()->status)->toBe('published')->and($post->fresh()->title)->toBe('Updated live article');
    $incomplete = app(SaveBlogPost::class)->handle(new Post, postDraftFixture(['slug' => 'incomplete-post', 'path' => '/incomplete-post/']));
    $this->post(route('admin.posts.publication', $incomplete), ['operation' => 'publish', 'content_version' => 1])->assertSessionHasErrors('publication');
    expect($incomplete->fresh()->status)->toBe('draft');
});

it('schedules, cancels and publishes due posts once', function () {
    $post = app(SaveBlogPost::class)->handle(new Post, postDraftFixture(['seo_title' => 'Synthetic SEO title', 'seo_description' => 'Synthetic search description.']));
    app(ChangeBlogPostPublication::class)->handle($post, ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => now()->addHour()->format('Y-m-d\TH:i')]);
    expect($post->fresh()->status)->toBe('scheduled')->and(app(PublishDuePosts::class)->handle())->toBe(['published' => 0, 'blocked' => 0]);
    app(ChangeBlogPostPublication::class)->handle($post->fresh(), ['operation' => 'cancel', 'content_version' => 2]);
    expect($post->fresh()->status)->toBe('draft')->and($post->fresh()->publication_locked_at)->not->toBeNull();
    app(ChangeBlogPostPublication::class)->handle($post->fresh(), ['operation' => 'schedule', 'content_version' => 3, 'scheduled_for' => now()->addHour()->format('Y-m-d\TH:i')]);
    $this->travel(2)->hours();
    expect(app(PublishDuePosts::class)->handle())->toBe(['published' => 1, 'blocked' => 0])->and($post->fresh()->status)->toBe('published')->and(app(PublishDuePosts::class)->handle())->toBe(['published' => 0, 'blocked' => 0]);
});

it('saves and restores isolated post SEO revisions', function () {
    $post = app(SaveBlogPost::class)->handle(new Post, postDraftFixture());
    $this->put(route('admin.posts.seo.update', $post), ['content_version' => 1, 'seo_title' => 'Synthetic search title', 'seo_description' => 'Synthetic search description.', 'robots' => 'index,follow', 'schema_type' => 'BlogPosting', 'sitemap_include' => true])->assertSessionHasNoErrors();
    $post->refresh();
    $revision = Revision::query()->where('revisionable_type', Post::class)->where('snapshot->kind', 'seo')->sole();
    expect($post->seo_title)->toBe('Synthetic search title')->and($post->schema_type)->toBe('BlogPosting')->and($post->content_version)->toBe(2);
    $this->put(route('admin.posts.seo.update', $post), ['content_version' => 2, 'seo_title' => 'Changed search title', 'seo_description' => 'Synthetic search description.', 'robots' => 'noindex', 'schema_type' => 'Article', 'sitemap_include' => false])->assertSessionHasNoErrors();
    $this->post(route('admin.posts.seo.restore', [$post, $revision]), ['content_version' => 3])->assertSessionHasNoErrors();
    expect($post->fresh()->seo_title)->toBe('Synthetic search title')->and($post->fresh()->robots)->toBe('index,follow')->and($post->fresh()->content_version)->toBe(4);
});

it('saves sanitized rich content with library images and renders optional contents', function () {
    $image = \App\Models\Media::query()->create(['disk' => 'public', 'path' => 'wp-content/uploads/qa.png',
        'original_name' => 'qa.png', 'mime' => 'image/png', 'sha256' => str_repeat('a', 64), 'size' => 100, 'width' => 32, 'height' => 24,
        'alt' => 'Library image', 'processing_status' => 'ready']);
    $body = '<h2>Article section</h2><p>Useful <strong>formatted text</strong>.</p><figure><img src="/wp-content/uploads/qa.png" alt="Article image"><figcaption>Saved caption</figcaption></figure><script>alert(1)</script>';
    $this->post(route('admin.posts.store'), postDraftFixture(['body' => $body,
        'path' => '/blog/rich-article', 'slug' => 'rich-article', 'featured_media_id' => $image->id,
        'seo_title' => 'Rich article', 'seo_description' => 'Rich article search description.']))->assertSessionHasNoErrors();
    $post = Post::query()->sole();
    expect($post->body_format)->toBe('html')->and($post->body)->toContain('<strong>', 'Saved caption')->not->toContain('<script');
    $this->post(route('admin.posts.publication', $post), ['operation' => 'publish', 'content_version' => 1])->assertSessionHasNoErrors();
    $this->get($post->path)->assertOk()->assertSee('Table of contents')->assertSee('article-section-1')->assertSee('Saved caption')->assertDontSee('<script>alert', false);
    $this->put(route('admin.posts.update', $post), postDraftFixture(['body' => $body, 'path' => $post->path,
        'slug' => $post->slug, 'content_version' => 2, 'show_toc' => false]))->assertSessionHasNoErrors();
    $this->get($post->path)->assertOk()->assertDontSee('Table of contents')->assertSee('Saved caption');
});

it('rejects new external images and unavailable library images', function () {
    $this->post(route('admin.posts.store'), postDraftFixture(['body' => '<p>Article</p><img src="https://example.test/unknown.png" alt="Unknown">']))->assertSessionHasErrors('body');
    $image = \App\Models\Media::query()->create(['disk' => 'public', 'path' => 'wp-content/uploads/queued.png',
        'original_name' => 'queued.png', 'mime' => 'image/png', 'sha256' => str_repeat('a', 64), 'size' => 100, 'alt' => 'Queued', 'processing_status' => 'queued']);
    $this->post(route('admin.posts.store'), postDraftFixture(['body' => '<p>Article</p><img src="/wp-content/uploads/queued.png" alt="Queued">']))->assertSessionHasErrors('body');
    expect(Post::query()->count())->toBe(0);
});

it('deletes a published article and restores it privately as a draft', function () {
    $post = app(SaveBlogPost::class)->handle(new Post, postDraftFixture(['path' => '/blog/delete-article', 'slug' => 'delete-article', 'seo_title' => 'Article', 'seo_description' => 'Article description.']));
    app(ChangeBlogPostPublication::class)->handle($post, ['operation' => 'publish', 'content_version' => 1]);
    $this->get($post->path)->assertOk();
    $this->delete(route('admin.posts.archive', $post), ['content_version' => 2])->assertSessionHasNoErrors();
    $this->get($post->path)->assertNotFound();
    $this->post(route('admin.posts.restore', $post), ['content_version' => 3])->assertSessionHasNoErrors();
    expect($post->fresh()->status)->toBe('draft')->and($post->fresh()->published_at)->toBeNull();
    $this->get($post->path)->assertNotFound();
});

it('keeps portfolio sources out of blog management and excludes future articles from public listings', function () {
    $post = Post::query()->create(['title' => 'Portfolio source hidden', 'path' => '/portfolio-source/example', 'slug' => 'source', 'body' => 'Source', 'status' => 'published', 'published_at' => now()]);
    $post->forceFill(['content_kind' => 'portfolio_source'])->save();
    Post::query()->create(['title' => 'Future article hidden', 'path' => '/future-article', 'slug' => 'future', 'body' => 'Future', 'status' => 'published', 'published_at' => now()->addDay()]);
    $this->get('/blog')->assertOk()->assertDontSee('Portfolio source hidden')->assertDontSee('Future article hidden');
    $this->get(route('admin.posts.index'))->assertOk()->assertDontSee('Portfolio source hidden');
    $this->get(route('admin.posts.edit', $post))->assertForbidden();
    $this->get('/future-article')->assertNotFound();
});

it('serves saved trailing slash article paths without a redirect loop', function (string $path) {
    $post = app(SaveBlogPost::class)->handle(new Post, postDraftFixture(['path' => $path, 'slug' => 'trailing-article', 'seo_title' => 'Article', 'seo_description' => 'Article description.']));
    app(ChangeBlogPostPublication::class)->handle($post, ['operation' => 'publish', 'content_version' => 1]);
    $this->get($path)->assertOk()->assertSee('Synthetic post');
})->with(['/trailing-article/', '/blog/trailing-article/']);

it('does not add a contents panel to articles by default', function () {
    $post = Post::query()->create(['title' => 'Reference article', 'slug' => 'reference-article',
        'path' => '/reference-article', 'body' => '<h2>Source heading</h2><p>Source article content.</p>',
        'status' => 'published', 'published_at' => now()]);
    expect($post->fresh()->show_toc)->toBeFalse();
    $this->get($post->path)->assertOk()->assertSee('Source heading')->assertDontSee('In this article')->assertDontSee('class="blog-post__toc"', false);
});
