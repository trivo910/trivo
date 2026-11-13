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
    $this->get(route('admin.posts.edit', $post))->assertOk()->assertSee('Saved content analysis')->assertSee('SEO score:')->assertSee('Post draft revision history')->assertSee('Restore this revision');
    $this->put(route('admin.posts.update', $post), postDraftFixture(['content_version' => 1, 'focus_keyword' => 'Synthetic', 'secondary_keywords_text' => "draft\nkeyword", 'seo_title' => 'Synthetic SEO title', 'seo_description' => 'Synthetic search description.']))->assertSessionHasNoErrors();
    expect($post->fresh()->secondary_keywords)->toBe(['draft', 'keyword']);
});

it('rejects HTML, unsafe paths and inactive assignment IDs', function (array $values, string $field) {
    $this->post(route('admin.posts.store'), postDraftFixture($values))->assertSessionHasErrors($field);
    expect(Post::query()->count())->toBe(0);
})->with([[['body' => '<p>Bad</p>'], 'body'], [['path' => '/wp-admin'], 'path'], [['author_id' => 99], 'author_id'], [['category_id' => 99], 'category_id'], [['tag_ids' => [99]], 'tag_ids']]);

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
    $this->delete(route('admin.posts.archive', $post), ['content_version' => 5])->assertForbidden();
});

it('publishes valid posts and rejects incomplete metadata', function () {
    $post = app(SaveBlogPost::class)->handle(new Post, postDraftFixture(['seo_title' => 'Synthetic SEO title', 'seo_description' => 'Synthetic search description.']));
    $this->post(route('admin.posts.publication', $post), ['operation' => 'publish', 'content_version' => 1])->assertSessionHasNoErrors();
    expect($post->fresh()->status)->toBe('published')->and($post->fresh()->published_at)->not->toBeNull()->and($post->fresh()->publication_locked_at)->not->toBeNull();
    $this->put(route('admin.posts.update', $post), postDraftFixture(['content_version' => 2, 'seo_title' => 'Synthetic SEO title', 'seo_description' => 'Synthetic search description.']))->assertForbidden();
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
    $this->delete(route('admin.posts.archive', $post), ['content_version' => 3])->assertForbidden();
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
