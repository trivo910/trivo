<?php

use App\Actions\Admin\ArchiveMedia;
use App\Actions\Admin\SaveServiceDraft;
use App\Actions\Admin\SaveServiceSeo;
use App\Models\Media;
use App\Models\Page;
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
    $this->service = Service::query()->create(['title' => 'Synthetic service', 'path' => '/synthetic-service', 'blocks' => [['type' => 'paragraph', 'text' => 'Source copy']],
        'body' => 'Existing source body', 'details' => ['source' => true], 'sort' => 7, 'status' => 'draft']);
});

/** @return array<string, mixed> */
function serviceSeoFixture(int $version = 1): array
{
    return ['content_version' => $version, 'seo_title' => 'Synthetic service SEO', 'seo_description' => 'Synthetic service metadata.',
        'canonical_url' => 'https://trivo.in/synthetic-service', 'robots' => 'index,follow', 'sitemap_include' => true, 'schema_type' => 'Service'];
}

it('renders a shared service SEO form with service schema and no content permission', function () {
    $this->get(route('admin.services.edit', $this->service))->assertOk()->assertSee('Edit service SEO')->assertDontSee('Save service draft');
    $this->get(route('admin.services.seo', $this->service))->assertOk()->assertSee('Service schema type')->assertSee('Save service SEO')->assertSee('value="Service"', false);
});

it('saves only service metadata and preserves source content identity and status', function () {
    $this->service->update(['schema_json' => ['@type' => 'Service']]);
    $this->put(route('admin.services.seo.update', $this->service), serviceSeoFixture() + ['title' => 'Injected', 'path' => '/hijacked', 'body' => 'Changed',
        'blocks' => [], 'sort' => 0, 'status' => 'published', 'legacy_wp_id' => 99, 'schema_json' => ['script' => 'bad']])->assertSessionHasNoErrors()->assertRedirect(route('admin.services.seo', $this->service));
    $service = $this->service->fresh();
    expect($service->getAttribute('seo_title'))->toBe('Synthetic service SEO')->and($service->title)->toBe('Synthetic service')->and($service->path)->toBe('/synthetic-service')
        ->and($service->body)->toBe('Existing source body')->and($service->details)->toBe(['source' => true])->and($service->blocks)->toHaveCount(1)
        ->and($service->sort)->toBe(7)->and($service->status)->toBe('draft')->and($service->legacy_wp_id)->toBeNull()
        ->and($service->getAttribute('schema_json'))->toBe(['@type' => 'Service'])->and($service->content_version)->toBe(2)
        ->and(Revision::query()->sole()->revisionable_type)->toBe(Service::class)->and(Revision::query()->sole()->snapshot['kind'])->toBe('seo');
    $this->get(route('admin.services.edit', $service))->assertDontSee('Restore service revision #');
});

it('shares safe metadata validation while rejecting incompatible schema types', function (array $values, string $field) {
    $this->put(route('admin.services.seo.update', $this->service), array_replace(serviceSeoFixture(), $values))->assertSessionHasErrors($field);
    expect(Revision::query()->count())->toBe(0)->and($this->service->fresh()->content_version)->toBe(1);
})->with([
    [['seo_title' => '<script>bad()</script>'], 'seo_title'], [['canonical_url' => 'https://evil.test/service'], 'canonical_url'],
    [['robots' => 'index,noindex'], 'robots'], [['robots' => 'noindex', 'sitemap_include' => true], 'sitemap_include'],
    [['schema_type' => 'AboutPage'], 'schema_type'], [['og_title' => ['malformed']], 'og_title'], [['twitter_image_id' => -1], 'twitter_image_id'],
]);

it('rejects stale SEO and content submissions using the same version', function () {
    app(SaveServiceSeo::class)->handle($this->service, serviceSeoFixture());
    $this->put(route('admin.services.seo.update', $this->service), serviceSeoFixture())->assertSessionHasErrors('content_version');
    $editor = adminAccount('Editor');
    $this->actingAs($editor)->withSession(verifiedAdminSession($editor));
    expect(fn () => app(SaveServiceDraft::class)->handle(['title' => 'Stale', 'path' => '/synthetic-service', 'sort' => 1, 'blocks' => [], 'content_version' => 1], $this->service))->toThrow(ValidationException::class);
    expect($this->service->fresh()->title)->toBe('Synthetic service');
});

it('restores SEO only for the matching model ID and revision kind', function () {
    $service = app(SaveServiceSeo::class)->handle($this->service, serviceSeoFixture());
    $revision = Revision::query()->sole();
    $service = app(SaveServiceSeo::class)->handle($service, array_replace(serviceSeoFixture(2), ['seo_title' => 'Newer SEO']));
    $this->post(route('admin.services.seo.restore', [$service, $revision]), ['content_version' => 3])->assertSessionHasNoErrors();
    expect($service->fresh()->getAttribute('seo_title'))->toBe('Synthetic service SEO')->and($service->fresh()->content_version)->toBe(4)->and($service->fresh()->path)->toBe('/synthetic-service');
    $other = Service::query()->create(['title' => 'Other', 'path' => '/other-service']);
    $this->post(route('admin.services.seo.restore', [$other, $revision]), ['content_version' => 1])->assertNotFound();
    $page = Page::query()->create(['title' => 'Page fixture', 'path' => '/page-fixture']);
    expect($page->id)->toBe($service->id);
    $pageRevision = Revision::query()->create(['revisionable_type' => Page::class, 'revisionable_id' => $page->id, 'snapshot' => $revision->snapshot]);
    $this->post(route('admin.services.seo.restore', [$service, $pageRevision]), ['content_version' => 4])->assertNotFound();
    $this->post(route('admin.pages.seo.restore', [$page, $revision]), ['content_version' => 1])->assertNotFound();
    $content = Revision::query()->create(['revisionable_type' => Service::class, 'revisionable_id' => $service->id, 'snapshot' => ['kind' => 'content', 'title' => 'Other']]);
    $this->post(route('admin.services.seo.restore', [$service, $content]), ['content_version' => 4])->assertNotFound();
    $editor = adminAccount('Editor');
    $this->actingAs($editor)->withSession(verifiedAdminSession($editor));
    $this->post(route('admin.services.revision', [$service, $revision]), ['content_version' => 4])->assertNotFound();
});

it('retains social image revision references and rechecks image readiness on restore', function () {
    $image = Media::query()->create(['disk' => 'public', 'path' => 'media/11111111-1111-4111-8111-111111111111/original.png', 'original_name' => 'fixture.png',
        'mime' => 'image/png', 'size' => 100, 'alt' => 'Synthetic image', 'sha256' => hash('sha256', 'fixture'), 'processing_status' => 'ready']);
    $service = app(SaveServiceSeo::class)->handle($this->service, serviceSeoFixture() + ['og_image_id' => $image->id]);
    $revision = Revision::query()->sole();
    app(SaveServiceSeo::class)->handle($service, serviceSeoFixture(2));
    $editor = adminAccount('Editor');
    $this->actingAs($editor)->withSession(verifiedAdminSession($editor));
    expect(fn () => app(ArchiveMedia::class)->handle($image))->toThrow(ValidationException::class);
    $image->update(['processing_status' => 'failed']);
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
    $this->post(route('admin.services.seo.restore', [$service, $revision]), ['content_version' => 3])->assertSessionHasErrors('og_image_id');
    expect($service->fresh()->content_version)->toBe(3);
});

it('edits published metadata without unlocking content and rejects archived services', function () {
    $this->service->update(['status' => 'published']);
    $this->put(route('admin.services.seo.update', $this->service), serviceSeoFixture())->assertSessionHasNoErrors();
    expect($this->service->fresh()->status)->toBe('published');
    $this->put(route('admin.services.update', $this->service), ['title' => 'Changed'])->assertForbidden();
    $this->service->delete();
    $this->get(route('admin.services.seo', $this->service))->assertNotFound();
});

it('enforces service-specific SEO permission authentication and CSRF', function () {
    $role = Role::findByName('SEO Manager');
    $role->revokePermissionTo('services.seo');
    $this->admin = $this->admin->fresh();
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
    $this->get(route('admin.services.seo', $this->service))->assertForbidden();
    $this->put(route('admin.services.seo.update', $this->service), serviceSeoFixture())->assertForbidden();
    $this->get(route('admin.pages.index'))->assertOk();
    $role->givePermissionTo('services.seo');
    $this->admin = $this->admin->fresh();
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
    $this->app['env'] = 'local';
    $this->put(route('admin.services.seo.update', $this->service), serviceSeoFixture())->assertStatus(419);
    auth()->logout();
    $this->flushSession();
    $this->get(route('admin.services.seo', $this->service))->assertRedirect(route('admin.login'));
    expect(Revision::query()->count())->toBe(0);
});
