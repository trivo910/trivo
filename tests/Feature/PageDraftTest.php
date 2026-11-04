<?php

use App\Actions\Admin\ArchiveMedia;
use App\Actions\Admin\SavePageDraft;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Revision;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->seed(RolePermissionSeeder::class);
    $this->admin = adminAccount('Editor');
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
});

/** @return array<string, mixed> */
function pageDraftFixture(): array
{
    return ['title' => 'Synthetic page', 'path' => '/Synthetic-page/', 'blocks' => [
        ['type' => 'heading', 'text' => 'Synthetic heading'], ['type' => 'paragraph', 'text' => "Paragraph one\nParagraph two"],
        ['type' => 'link', 'label' => 'Read more', 'url' => '/about?from=fixture#section'],
    ]];
}

it('creates a draft with an exact path and revision without accepting publishing or SEO fields', function () {
    $this->post(route('admin.pages.store'), array_replace(pageDraftFixture(), ['status' => 'published', 'published_at' => now(),
        'legacy_wp_id' => 123, 'seo_title' => 'Injected', 'template' => '../../secret', 'content_version' => 99]))->assertSessionHasNoErrors()->assertRedirect();
    $page = Page::query()->sole();
    expect($page->path)->toBe('/Synthetic-page/')->and($page->status)->toBe('draft')->and($page->published_at)->toBeNull()
        ->and($page->legacy_wp_id)->toBeNull()->and($page->seo_title)->toBeNull()->and($page->template)->toBe('page')
        ->and($page->content_version)->toBe(1)->and(Revision::query()->sole()->snapshot['blocks'])->toBe(pageDraftFixture()['blocks']);
    $this->get(route('admin.pages.edit', $page))->assertOk()->assertSee('Synthetic heading')->assertSee('Restore revision');
    $this->get('/Synthetic-page/')->assertNotFound();
});

it('supports the native hidden JSON payload and rejects malformed section JSON', function () {
    $data = pageDraftFixture();
    unset($data['blocks']);
    $this->post(route('admin.pages.store'), $data + ['blocks_json' => json_encode(pageDraftFixture()['blocks'])])->assertSessionHasNoErrors();
    expect(Page::query()->sole()->blocks)->toHaveCount(3);
    $this->post(route('admin.pages.store'), array_replace($data, ['path' => '/another', 'blocks_json' => '{bad']))->assertSessionHasErrors('blocks');
    expect(Page::query()->count())->toBe(1);
});

it('saves revisions and rejects stale edits without overwriting newer content', function () {
    $page = app(SavePageDraft::class)->handle(pageDraftFixture());
    $this->put(route('admin.pages.update', $page), array_replace(pageDraftFixture(), ['title' => 'New title', 'content_version' => 1]))->assertSessionHasNoErrors();
    $this->put(route('admin.pages.update', $page), array_replace(pageDraftFixture(), ['title' => 'Stale title', 'content_version' => 1]))->assertSessionHasErrors('content_version');
    expect($page->fresh()->title)->toBe('New title')->and($page->fresh()->content_version)->toBe(2)->and(Revision::query()->count())->toBe(2);
});

it('restores revision content as a new version while retaining the current path and SEO', function () {
    $page = app(SavePageDraft::class)->handle(pageDraftFixture());
    $revision = Revision::query()->sole();
    $page->update(['seo_title' => 'Existing SEO']);
    $page = app(SavePageDraft::class)->handle(array_replace(pageDraftFixture(), ['title' => 'Changed', 'path' => '/changed-path', 'content_version' => 1]), $page);
    $this->post(route('admin.pages.revision', [$page, $revision]), ['content_version' => 2])->assertSessionHasNoErrors();
    expect($page->fresh()->title)->toBe('Synthetic page')->and($page->fresh()->path)->toBe('/changed-path')
        ->and($page->fresh()->seo_title)->toBe('Existing SEO')->and($page->fresh()->content_version)->toBe(3)->and(Revision::query()->count())->toBe(3);
    $other = app(SavePageDraft::class)->handle(array_replace(pageDraftFixture(), ['path' => '/other']));
    $this->post(route('admin.pages.revision', [$other, $revision]), ['content_version' => 1])->assertNotFound();
});

it('rejects unsafe reserved or ambiguous paths', function (string $path) {
    $this->post(route('admin.pages.store'), array_replace(pageDraftFixture(), ['path' => $path]))->assertSessionHasErrors('path');
    expect(Page::query()->count())->toBe(0);
})->with(['//evil.test', '/a/../b', '/up', '/robots.txt', '/wp-content/file', '/storage/file', '/sitemap.xml', '/wp-admin',
    '/foo?bar=1', '/foo#bar', '/%2f%2fevil.test', '/x.php', '/%75p', '/.env', '/bad path']);

it('reserves homepage paths trailing slash aliases case aliases and cross-content collisions', function () {
    $page = app(SavePageDraft::class)->handle(array_replace(pageDraftFixture(), ['path' => '/']));
    $this->post(route('admin.pages.store'), array_replace(pageDraftFixture(), ['path' => '/']))->assertSessionHasErrors('path');
    Post::query()->create(['title' => 'Synthetic post', 'path' => '/Existing-post', 'slug' => 'existing-post', 'body' => 'Synthetic body']);
    $this->post(route('admin.pages.store'), array_replace(pageDraftFixture(), ['path' => '/existing-post/']))->assertSessionHasErrors('path');
    Redirect::query()->create(['from_path' => '/old-page', 'to_url' => '/', 'status_code' => 301]);
    $this->post(route('admin.pages.store'), array_replace(pageDraftFixture(), ['path' => '/OLD-page/']))->assertSessionHasErrors('path');
    expect(Page::query()->count())->toBe(1)->and($page->path)->toBe('/');
});

it('rejects unsafe typed blocks and excess sections', function (array $blocks) {
    $this->post(route('admin.pages.store'), array_replace(pageDraftFixture(), ['blocks' => $blocks]))->assertSessionHasErrors();
    expect(Page::query()->count())->toBe(0)->and(Revision::query()->count())->toBe(0);
})->with([
    [[['type' => 'html', 'text' => '<script>alert(1)</script>']]],
    [[['type' => 'paragraph', 'text' => '<img src=x onerror=alert(1)>']]],
    [[['type' => 'heading', 'text' => ['malformed']]]],
    [[['type' => 'paragraph', 'text' => 'text', 'script' => 'bad()']]],
    [[['type' => 'link', 'label' => 'Link', 'url' => 'javascript:alert(1)']]],
    [[['type' => 'link', 'label' => 'Link', 'url' => 'https://user:pass@example.test/']]],
    [[['type' => 'link', 'label' => 'Link', 'url' => '//evil.test/path']]],
    [[['type' => 'heading', 'text' => '']]],
    [array_fill(0, 51, ['type' => 'paragraph', 'text' => 'Too many'])],
    [['named' => ['type' => 'paragraph', 'text' => 'Not a list']]],
]);

it('rechecks images and keeps both active sections and revision images protected', function () {
    $image = Media::query()->create(['disk' => 'public', 'path' => 'media/11111111-1111-4111-8111-111111111111/original.png',
        'original_name' => 'fixture.png', 'mime' => 'image/png', 'width' => 100, 'height' => 50, 'size' => 100, 'sha256' => hash('sha256', 'fixture'),
        'alt' => 'Fixture image', 'processing_status' => 'ready']);
    $page = app(SavePageDraft::class)->handle(array_replace(pageDraftFixture(), ['blocks' => [['type' => 'image', 'media_id' => $image->id]]]));
    expect(fn () => app(ArchiveMedia::class)->handle($image))->toThrow(ValidationException::class);
    app(SavePageDraft::class)->handle(array_replace(pageDraftFixture(), ['content_version' => 1, 'blocks' => []]), $page);
    expect(fn () => app(ArchiveMedia::class)->handle($image))->toThrow(ValidationException::class);
    $image->update(['processing_status' => 'failed']);
    $this->post(route('admin.pages.store'), array_replace(pageDraftFixture(), ['path' => '/bad-image', 'blocks' => [['type' => 'image', 'media_id' => $image->id]]]))->assertSessionHasErrors('blocks.0.media_id');
    expect(Page::query()->count())->toBe(1);
});

it('archives and restores only unpublished drafts and retains paths and history', function () {
    $page = app(SavePageDraft::class)->handle(pageDraftFixture());
    $this->delete(route('admin.pages.archive', $page), ['content_version' => 1])->assertSessionHasNoErrors();
    expect($page->fresh()->trashed())->toBeTrue()->and(Revision::query()->count())->toBe(1);
    $this->post(route('admin.pages.store'), pageDraftFixture())->assertSessionHasErrors('path');
    $this->get(route('admin.pages.edit', $page))->assertOk()->assertSee('Restore page draft');
    $this->post(route('admin.pages.restore', $page), ['content_version' => 2])->assertSessionHasNoErrors();
    expect($page->fresh()->trashed())->toBeFalse()->and($page->fresh()->content_version)->toBe(3);
});

it('protects published scheduled previously published and imported paths', function () {
    foreach (['published', 'scheduled'] as $status) {
        $page = Page::query()->create(['title' => $status, 'path' => '/'.$status, 'status' => $status]);
        $this->get(route('admin.pages.edit', $page))->assertOk()->assertDontSee('Save page draft');
        $this->put(route('admin.pages.update', $page), pageDraftFixture() + ['content_version' => 1])->assertForbidden();
        $this->delete(route('admin.pages.archive', $page), ['content_version' => 1])->assertForbidden();
    }
    $page = Page::query()->create(['title' => 'Imported', 'path' => '/imported', 'status' => 'draft', 'legacy_wp_id' => 101]);
    $this->put(route('admin.pages.update', $page), pageDraftFixture() + ['content_version' => 1])->assertSessionHasErrors('path');
    $this->delete(route('admin.pages.archive', $page), ['content_version' => 1])->assertForbidden();
    $page->update(['legacy_wp_id' => null, 'published_at' => now()->subDay()]);
    $this->put(route('admin.pages.update', $page), pageDraftFixture() + ['content_version' => 1])->assertForbidden();
});

it('permits SEO managers to read but not mutate page drafts', function () {
    $page = app(SavePageDraft::class)->handle(pageDraftFixture());
    $revision = Revision::query()->sole();
    $user = adminAccount('SEO Manager');
    $this->actingAs($user)->withSession(verifiedAdminSession($user));
    $this->get(route('admin.pages.index'))->assertOk()->assertDontSee('Create page draft');
    $this->get(route('admin.pages.edit', $page))->assertOk()->assertDontSee('Save page draft');
    $this->get(route('admin.pages.create'))->assertForbidden();
    $this->post(route('admin.pages.store'), pageDraftFixture())->assertForbidden();
    $this->put(route('admin.pages.update', $page), pageDraftFixture() + ['content_version' => 1])->assertForbidden();
    $this->delete(route('admin.pages.archive', $page), ['content_version' => 1])->assertForbidden();
    $this->post(route('admin.pages.revision', [$page, $revision]), ['content_version' => 1])->assertForbidden();
});

it('requires authentication and CSRF and does not save without them', function () {
    auth()->logout();
    $this->flushSession();
    $this->get(route('admin.pages.index'))->assertRedirect(route('admin.login'));
    $this->post(route('admin.pages.store'), pageDraftFixture())->assertRedirect(route('admin.login'));
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
    $this->app['env'] = 'local';
    $this->post(route('admin.pages.store'), pageDraftFixture())->assertStatus(419);
    expect(Page::query()->count())->toBe(0);
});
