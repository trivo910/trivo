<?php

use App\Actions\Admin\ArchiveMedia;
use App\Actions\Admin\SavePortfolioDraft;
use App\Actions\Admin\SavePortfolioSeo;
use App\Models\Media;
use App\Models\Page;
use App\Models\PortfolioItem;
use App\Models\Revision;
use App\Models\Service;
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
    $this->item = PortfolioItem::query()->create(['title' => 'Synthetic case study', 'path' => '/case-study', 'status' => 'draft', 'sort' => 7,
        'blocks' => [['type' => 'paragraph', 'text' => 'Source copy']], 'body' => 'Source body', 'details' => ['source' => true]]);
});

/** @return array<string, mixed> */
function portfolioSeoFixture(int $version = 1): array
{
    return ['content_version' => $version, 'seo_title' => 'Synthetic case-study SEO', 'seo_description' => 'Synthetic case-study description.',
        'canonical_url' => 'https://trivo.in/case-study', 'robots' => 'index,follow', 'sitemap_include' => true, 'schema_type' => 'CreativeWork'];
}

it('renders portfolio SEO for SEO managers while leaving content read-only', function () {
    $this->get(route('admin.portfolio.edit', $this->item))->assertOk()->assertSee('Edit portfolio SEO')->assertDontSee('Save portfolio draft');
    $this->get(route('admin.portfolio.seo', $this->item))->assertOk()->assertSee('Portfolio schema type')->assertSee('value="CreativeWork"', false)->assertSee('Save portfolio SEO');
});

it('saves only metadata and creates a portfolio-scoped SEO revision', function () {
    $this->item->update(['schema_json' => ['@type' => 'CreativeWork']]);
    $this->put(route('admin.portfolio.seo.update', $this->item), portfolioSeoFixture() + ['title' => 'Injected', 'path' => '/changed', 'body' => 'Changed',
        'details' => [], 'blocks' => [], 'sort' => 0, 'status' => 'published', 'legacy_wp_id' => 123, 'schema_json' => ['bad' => true]])->assertSessionHasNoErrors();
    $item = $this->item->fresh();
    expect($item->getAttribute('seo_title'))->toBe('Synthetic case-study SEO')->and($item->title)->toBe('Synthetic case study')->and($item->path)->toBe('/case-study')
        ->and($item->getAttribute('body'))->toBe('Source body')->and($item->details)->toBe(['source' => true])->and($item->blocks)->toHaveCount(1)
        ->and($item->sort)->toBe(7)->and($item->status)->toBe('draft')->and($item->legacy_wp_id)->toBeNull()->and($item->content_version)->toBe(2)
        ->and($item->getAttribute('schema_json'))->toBe(['@type' => 'CreativeWork'])->and(Revision::query()->sole()->revisionable_type)->toBe(PortfolioItem::class);
    $this->get(route('admin.portfolio.edit', $item))->assertDontSee('Restore portfolio revision #');
});

it('validates metadata canonicals robots and portfolio schema choices', function (array $values, string $field) {
    $this->put(route('admin.portfolio.seo.update', $this->item), array_replace(portfolioSeoFixture(), $values))->assertSessionHasErrors($field);
    expect(Revision::query()->count())->toBe(0)->and($this->item->fresh()->content_version)->toBe(1);
})->with([
    [['seo_title' => '<script>bad()</script>'], 'seo_title'], [['canonical_url' => 'https://evil.test/case-study'], 'canonical_url'],
    [['robots' => 'index,noindex'], 'robots'], [['robots' => 'noindex', 'sitemap_include' => true], 'sitemap_include'],
    [['schema_type' => 'Service'], 'schema_type'], [['schema_type' => 'AboutPage'], 'schema_type'], [['og_title' => ['malformed']], 'og_title'],
]);

it('rejects stale metadata and content edits through the shared version', function () {
    app(SavePortfolioSeo::class)->handle($this->item, portfolioSeoFixture());
    $this->put(route('admin.portfolio.seo.update', $this->item), portfolioSeoFixture())->assertSessionHasErrors('content_version');
    $editor = adminAccount('Editor');
    $this->actingAs($editor)->withSession(verifiedAdminSession($editor));
    expect(fn () => app(SavePortfolioDraft::class)->handle(['title' => 'Stale', 'path' => '/case-study', 'blocks' => [], 'sort' => 0, 'content_version' => 1], $this->item))->toThrow(ValidationException::class);
});

it('restores only matching metadata revisions and blocks equal-ID cross-model restores', function () {
    $item = app(SavePortfolioSeo::class)->handle($this->item, portfolioSeoFixture());
    $revision = Revision::query()->sole();
    app(SavePortfolioSeo::class)->handle($item, array_replace(portfolioSeoFixture(2), ['seo_title' => 'Newer SEO']));
    $this->post(route('admin.portfolio.seo.restore', [$item, $revision]), ['content_version' => 3])->assertSessionHasNoErrors();
    expect($item->fresh()->getAttribute('seo_title'))->toBe('Synthetic case-study SEO')->and($item->fresh()->content_version)->toBe(4)->and($item->fresh()->path)->toBe('/case-study');
    $other = PortfolioItem::query()->create(['title' => 'Other', 'path' => '/other-case-study']);
    $this->post(route('admin.portfolio.seo.restore', [$other, $revision]), ['content_version' => 1])->assertNotFound();
    $service = Service::query()->create(['title' => 'Service fixture', 'path' => '/service-fixture']);
    $page = Page::query()->create(['title' => 'Page fixture', 'path' => '/page-fixture']);
    expect($service->id)->toBe($item->id)->and($page->id)->toBe($item->id);
    $wrong = Revision::query()->create(['revisionable_type' => Service::class, 'revisionable_id' => $service->id, 'snapshot' => $revision->snapshot]);
    $this->post(route('admin.portfolio.seo.restore', [$item, $wrong]), ['content_version' => 4])->assertNotFound();
    $this->post(route('admin.services.seo.restore', [$service, $revision]), ['content_version' => 1])->assertNotFound();
    $this->post(route('admin.pages.seo.restore', [$page, $revision]), ['content_version' => 1])->assertNotFound();
    $content = Revision::query()->create(['revisionable_type' => PortfolioItem::class, 'revisionable_id' => $item->id, 'snapshot' => ['kind' => 'content']]);
    $this->post(route('admin.portfolio.seo.restore', [$item, $content]), ['content_version' => 4])->assertNotFound();
    $editor = adminAccount('Editor');
    $this->actingAs($editor)->withSession(verifiedAdminSession($editor));
    $this->post(route('admin.portfolio.revision', [$item, $revision]), ['content_version' => 4])->assertNotFound();
});

it('protects historical social images and rechecks readiness during metadata restore', function () {
    $image = Media::query()->create(['disk' => 'public', 'path' => 'media/11111111-1111-4111-8111-111111111111/original.png', 'original_name' => 'fixture.png',
        'mime' => 'image/png', 'size' => 100, 'alt' => 'Synthetic image', 'sha256' => hash('sha256', 'fixture'), 'processing_status' => 'ready']);
    $item = app(SavePortfolioSeo::class)->handle($this->item, portfolioSeoFixture() + ['og_image_id' => $image->id]);
    $revision = Revision::query()->sole();
    app(SavePortfolioSeo::class)->handle($item, portfolioSeoFixture(2));
    $editor = adminAccount('Editor');
    $this->actingAs($editor)->withSession(verifiedAdminSession($editor));
    expect(fn () => app(ArchiveMedia::class)->handle($image))->toThrow(ValidationException::class);
    $image->update(['processing_status' => 'failed']);
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
    $this->post(route('admin.portfolio.seo.restore', [$item, $revision]), ['content_version' => 3])->assertSessionHasErrors('og_image_id');
    expect($item->fresh()->content_version)->toBe(3);
});

it('edits published metadata while excluding archived records and preserving status', function () {
    $this->item->update(['status' => 'published']);
    $this->put(route('admin.portfolio.seo.update', $this->item), portfolioSeoFixture())->assertSessionHasNoErrors();
    expect($this->item->fresh()->status)->toBe('published');
    $this->put(route('admin.portfolio.update', $this->item), ['title' => 'Changed'])->assertForbidden();
    $this->item->delete();
    $this->get(route('admin.portfolio.seo', $this->item))->assertNotFound();
});

it('requires portfolio-specific SEO permission authenticated sessions and CSRF', function () {
    $role = Role::findByName('SEO Manager');
    $role->revokePermissionTo('portfolio.seo');
    $this->admin = $this->admin->fresh();
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
    $this->get(route('admin.portfolio.seo', $this->item))->assertForbidden();
    $this->put(route('admin.portfolio.seo.update', $this->item), portfolioSeoFixture())->assertForbidden();
    $role->givePermissionTo('portfolio.seo');
    $this->admin = $this->admin->fresh();
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
    $this->app['env'] = 'local';
    $this->put(route('admin.portfolio.seo.update', $this->item), portfolioSeoFixture())->assertStatus(419);
    auth()->logout();
    $this->flushSession();
    $this->get(route('admin.portfolio.seo', $this->item))->assertRedirect(route('admin.login'));
    expect(Revision::query()->count())->toBe(0);
});
