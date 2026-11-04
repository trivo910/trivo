<?php

use App\Actions\Admin\ArchiveMedia;
use App\Actions\Admin\SavePageDraft;
use App\Actions\Admin\SavePageSeo;
use App\Models\Media;
use App\Models\Page;
use App\Models\Revision;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->seed(RolePermissionSeeder::class);
    $this->admin = adminAccount('SEO Manager');
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
    $this->page = Page::query()->create(['title' => 'Synthetic page', 'path' => '/synthetic-page', 'blocks' => [['type' => 'paragraph', 'text' => 'Original copy']], 'status' => 'draft']);
});

/** @return array<string, mixed> */
function pageSeoFixture(int $version = 1): array
{
    return ['content_version' => $version, 'seo_title' => 'Synthetic SEO title', 'seo_description' => 'Synthetic source description.',
        'canonical_url' => 'https://trivo.in/synthetic-page', 'robots' => 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1',
        'sitemap_include' => true, 'sitemap_priority' => '0.7', 'schema_type' => 'WebPage', 'og_title' => 'Synthetic social title'];
}

it('saves only metadata and creates a separate SEO revision without mutating content', function () {
    $this->page->update(['schema_json' => ['@type' => 'WebPage']]);
    $this->put(route('admin.pages.seo.update', $this->page), pageSeoFixture() + ['title' => 'Injected content', 'path' => '/hijacked',
        'status' => 'published', 'blocks' => [], 'schema_json' => ['script' => 'bad'], 'noindex' => true])->assertSessionHasNoErrors()->assertRedirect(route('admin.pages.seo', $this->page));
    $page = $this->page->fresh();
    expect($page->seo_title)->toBe('Synthetic SEO title')->and($page->title)->toBe('Synthetic page')->and($page->path)->toBe('/synthetic-page')
        ->and($page->status)->toBe('draft')->and($page->blocks)->toHaveCount(1)->and($page->noindex)->toBeFalse()
        ->and($page->schema_json)->toBe(['@type' => 'WebPage'])->and($page->content_version)->toBe(2)
        ->and(Revision::query()->sole()->snapshot['kind'])->toBe('seo');
    $this->get(route('admin.pages.seo', $page))->assertOk()->assertSee('Synthetic SEO title')->assertSee('Restore SEO revision');
    $this->get(route('admin.pages.edit', $page))->assertDontSee('Restore revision #');
});

it('rejects stale SEO writes and conflicting stale content edits', function () {
    $this->put(route('admin.pages.seo.update', $this->page), pageSeoFixture())->assertSessionHasNoErrors();
    $this->put(route('admin.pages.seo.update', $this->page), array_replace(pageSeoFixture(), ['seo_title' => 'Stale']))->assertSessionHasErrors('content_version');
    $editor = adminAccount('Editor');
    $this->actingAs($editor)->withSession(verifiedAdminSession($editor));
    expect(fn () => app(SavePageDraft::class)->handle(['title' => 'Stale content', 'path' => '/synthetic-page', 'blocks' => [], 'content_version' => 1], $this->page))->toThrow(ValidationException::class);
    expect($this->page->fresh()->seo_title)->toBe('Synthetic SEO title')->and(Revision::query()->count())->toBe(1);
});

it('restores only matching SEO revisions while retaining content path publication and schema JSON', function () {
    $page = app(SavePageSeo::class)->handle($this->page, pageSeoFixture());
    $revision = Revision::query()->sole();
    $page = app(SavePageSeo::class)->handle($page, array_replace(pageSeoFixture(2), ['seo_title' => 'Newer SEO']));
    $this->post(route('admin.pages.seo.restore', [$page, $revision]), ['content_version' => 3])->assertSessionHasNoErrors();
    expect($page->fresh()->seo_title)->toBe('Synthetic SEO title')->and($page->fresh()->content_version)->toBe(4)->and($page->fresh()->title)->toBe('Synthetic page');
    $other = Page::query()->create(['title' => 'Other page', 'path' => '/other']);
    $this->post(route('admin.pages.seo.restore', [$other, $revision]), ['content_version' => 1])->assertNotFound();
    $content = Revision::query()->create(['revisionable_type' => Page::class, 'revisionable_id' => $page->id, 'snapshot' => ['kind' => 'content', 'title' => 'Other', 'blocks' => []]]);
    $this->post(route('admin.pages.seo.restore', [$page, $content]), ['content_version' => 4])->assertNotFound();
    $editor = adminAccount('Editor');
    $this->actingAs($editor)->withSession(verifiedAdminSession($editor));
    $this->post(route('admin.pages.revision', [$page, $revision]), ['content_version' => 4])->assertNotFound();
});

it('rejects unsafe metadata canonical URLs robots and sitemap values', function (array $values, string $field) {
    $this->put(route('admin.pages.seo.update', $this->page), array_replace(pageSeoFixture(), $values))->assertSessionHasErrors($field);
    expect(Revision::query()->count())->toBe(0)->and($this->page->fresh()->content_version)->toBe(1);
})->with([
    [['seo_title' => '<script>bad()</script>'], 'seo_title'], [['seo_description' => ['malformed']], 'seo_description'],
    [['og_title' => "Header\r\nInjected"], 'og_title'], [['canonical_url' => 'https://evil.test/page'], 'canonical_url'],
    [['canonical_url' => 'https://trivo.in@evil.test/page'], 'canonical_url'], [['canonical_url' => 'https://user:pass@trivo.in/page'], 'canonical_url'],
    [['canonical_url' => 'http://trivo.in/page'], 'canonical_url'], [['canonical_url' => 'https://trivo.in/page?query=1'], 'canonical_url'],
    [['canonical_url' => 'https://trivo.in/page#fragment'], 'canonical_url'], [['canonical_url' => 'https://trivo.in/wp-admin'], 'canonical_url'],
    [['robots' => 'index,noindex'], 'robots'], [['robots' => 'follow,nofollow'], 'robots'], [['robots' => 'max-snippet:10,max-snippet:20'], 'robots'],
    [['robots' => 'index,<meta>'], 'robots'], [['robots' => ['malformed']], 'robots'],
    [['robots' => 'noindex,follow', 'sitemap_include' => true], 'sitemap_include'],
    [['sitemap_priority' => '1.1'], 'sitemap_priority'], [['schema_type' => 'Script'], 'schema_type'],
]);

it('derives robots flags consistently and explicitly clears optional overrides', function () {
    $this->put(route('admin.pages.seo.update', $this->page), array_replace(pageSeoFixture(), ['robots' => 'NOINDEX, nofollow, noindex', 'sitemap_include' => false]))->assertSessionHasNoErrors();
    expect($this->page->fresh()->robots)->toBe('noindex,nofollow')->and($this->page->fresh()->noindex)->toBeTrue()->and($this->page->fresh()->nofollow)->toBeTrue();
    $this->put(route('admin.pages.seo.update', $this->page), ['content_version' => 2, 'sitemap_include' => true, 'seo_title' => '', 'robots' => ''])->assertSessionHasNoErrors();
    expect($this->page->fresh()->seo_title)->toBeNull()->and($this->page->fresh()->robots)->toBeNull()->and($this->page->fresh()->noindex)->toBeFalse();
});

it('locks valid social images and retains their revision references after clearing', function () {
    $image = Media::query()->create(['disk' => 'public', 'path' => 'media/11111111-1111-4111-8111-111111111111/original.png',
        'original_name' => 'fixture.png', 'mime' => 'image/png', 'width' => 1200, 'height' => 630, 'size' => 100, 'sha256' => hash('sha256', 'fixture'),
        'alt' => 'Synthetic social image', 'processing_status' => 'ready']);
    $this->put(route('admin.pages.seo.update', $this->page), pageSeoFixture() + ['og_image_id' => $image->id])->assertSessionHasNoErrors();
    $this->put(route('admin.pages.seo.update', $this->page), pageSeoFixture(2))->assertSessionHasNoErrors();
    $editor = adminAccount('Editor');
    $this->actingAs($editor)->withSession(verifiedAdminSession($editor));
    expect(fn () => app(ArchiveMedia::class)->handle($image))->toThrow(ValidationException::class);
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
    $image->update(['processing_status' => 'failed']);
    $this->put(route('admin.pages.seo.update', $this->page), pageSeoFixture(3) + ['twitter_image_id' => $image->id])->assertSessionHasErrors('twitter_image_id');
    expect($this->page->fresh()->content_version)->toBe(3);
});

it('allows SEO on published pages without granting content editing and blocks archived pages', function () {
    $this->page->update(['status' => 'published', 'published_at' => now()]);
    $this->put(route('admin.pages.seo.update', $this->page), pageSeoFixture())->assertSessionHasNoErrors();
    expect($this->page->fresh()->status)->toBe('published')->and($this->page->fresh()->published_at)->not->toBeNull();
    $this->put(route('admin.pages.update', $this->page), ['title' => 'Changed'])->assertForbidden();
    $this->page->delete();
    $this->get(route('admin.pages.seo', $this->page))->assertNotFound();
});

it('requires SEO permission authentication and CSRF', function () {
    $role = Role::findByName('SEO Manager');
    $role->revokePermissionTo('pages.seo');
    $this->admin = $this->admin->fresh();
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
    $this->get(route('admin.pages.seo', $this->page))->assertForbidden();
    $this->put(route('admin.pages.seo.update', $this->page), pageSeoFixture())->assertForbidden();
    $role->givePermissionTo('pages.seo');
    $this->admin = $this->admin->fresh();
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
    $this->app['env'] = 'local';
    $this->put(route('admin.pages.seo.update', $this->page), pageSeoFixture())->assertStatus(419);
    auth()->logout();
    $this->flushSession();
    $this->get(route('admin.pages.seo', $this->page))->assertRedirect(route('admin.login'));
    expect(Revision::query()->count())->toBe(0);
});
