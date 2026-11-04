<?php

use App\Actions\Admin\ArchiveMedia;
use App\Actions\Admin\SavePageDraft;
use App\Actions\Admin\SaveServiceDraft;
use App\Models\Media;
use App\Models\Page;
use App\Models\PortfolioItem;
use App\Models\Revision;
use App\Models\Service;
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
function serviceDraftFixture(): array
{
    return ['title' => 'Synthetic service', 'path' => '/Synthetic-service/', 'sort' => 10, 'blocks' => [
        ['type' => 'heading', 'text' => 'Synthetic service heading'], ['type' => 'paragraph', 'text' => 'Synthetic service copy.'],
    ]];
}

it('creates only a service draft with exact paths and a revision', function () {
    $this->post(route('admin.services.store'), serviceDraftFixture() + ['status' => 'published', 'body' => '<script>bad()</script>',
        'details' => ['injected' => true], 'seo_title' => 'Injected SEO', 'legacy_wp_id' => 123])->assertSessionHasNoErrors()->assertRedirect();
    $service = Service::query()->sole();
    expect($service->status)->toBe('draft')->and($service->path)->toBe('/Synthetic-service/')->and($service->body)->toBeNull()
        ->and($service->details)->toBeNull()->and($service->legacy_wp_id)->toBeNull()->and($service->getAttribute('seo_title'))->toBeNull()
        ->and($service->content_version)->toBe(1)->and(Revision::query()->sole()->revisionable_type)->toBe(Service::class);
    $this->get(route('admin.services.edit', $service))->assertOk()->assertSee('Synthetic service heading')->assertSee('Service sections');
    $this->get('/Synthetic-service/')->assertNotFound();
});

it('shares JSON section validation and rejects HTML unknown keys malformed payloads and bad ordering', function (array $values, string $field) {
    $this->post(route('admin.services.store'), array_replace(serviceDraftFixture(), $values))->assertSessionHasErrors($field);
    expect(Service::query()->count())->toBe(0)->and(Revision::query()->count())->toBe(0);
})->with([
    [['blocks_json' => '{bad'], 'blocks'], [['blocks' => [['type' => 'paragraph', 'text' => '<img src=x onerror=bad()>']]], 'blocks.0.text'],
    [['blocks' => [['type' => 'html', 'text' => 'bad']]], 'blocks.0.type'], [['blocks' => [['type' => 'paragraph', 'text' => 'ok', 'script' => 'bad()']]], 'blocks.0'],
    [['title' => ['malformed']], 'title'], [['sort' => -1], 'sort'], [['sort' => 10001], 'sort'], [['media_id' => ['malformed']], 'media_id'],
    [['path' => '/'], 'path'], [['path' => '/wp-admin'], 'path'],
]);

it('updates versioned drafts preserves source fields and rejects stale saves', function () {
    $service = app(SaveServiceDraft::class)->handle(serviceDraftFixture());
    $service->update(['body' => 'Existing source body', 'details' => ['source' => true], 'seo_title' => 'Existing SEO']);
    $data = array_replace(serviceDraftFixture(), ['title' => 'New service title', 'sort' => 2, 'content_version' => 1]);
    $this->put(route('admin.services.update', $service), $data)->assertSessionHasNoErrors();
    $this->put(route('admin.services.update', $service), $data)->assertSessionHasErrors('content_version');
    expect($service->fresh()->title)->toBe('New service title')->and($service->fresh()->body)->toBe('Existing source body')
        ->and($service->fresh()->getAttribute('seo_title'))->toBe('Existing SEO')->and($service->fresh()->content_version)->toBe(2)->and(Revision::query()->count())->toBe(2);
});

it('prevents cross-module path collisions even when numeric IDs match and reserves aliases', function () {
    $page = Page::query()->create(['title' => 'Existing page', 'path' => '/occupied']);
    $service = app(SaveServiceDraft::class)->handle(serviceDraftFixture());
    expect($page->id)->toBe($service->id);
    $this->put(route('admin.services.update', $service), array_replace(serviceDraftFixture(), ['path' => '/OCCUPIED/', 'content_version' => 1]))->assertSessionHasErrors('path');
    expect(fn () => app(SavePageDraft::class)->handle(['title' => 'Collision', 'path' => '/synthetic-service', 'blocks' => []]))->toThrow(ValidationException::class);
    PortfolioItem::query()->create(['title' => 'Portfolio fixture', 'path' => '/portfolio-path']);
    $this->post(route('admin.services.store'), array_replace(serviceDraftFixture(), ['path' => '/PORTFOLIO-path/']))->assertSessionHasErrors('path');
    $this->delete(route('admin.services.archive', $service), ['content_version' => 1])->assertSessionHasNoErrors();
    $this->post(route('admin.services.store'), serviceDraftFixture())->assertSessionHasErrors('path');
    expect(fn () => app(SavePageDraft::class)->handle(['title' => 'Archived collision', 'path' => '/synthetic-service/', 'blocks' => []]))->toThrow(ValidationException::class);
});

it('restores the correct service revision with immutable path and source metadata', function () {
    $service = app(SaveServiceDraft::class)->handle(serviceDraftFixture());
    $revision = Revision::query()->sole();
    $service = app(SaveServiceDraft::class)->handle(array_replace(serviceDraftFixture(), ['title' => 'New title', 'path' => '/new-service-path', 'sort' => 1, 'content_version' => 1]), $service);
    $this->post(route('admin.services.revision', [$service, $revision]), ['content_version' => 2])->assertSessionHasNoErrors();
    expect($service->fresh()->title)->toBe('Synthetic service')->and($service->fresh()->path)->toBe('/new-service-path')->and($service->fresh()->sort)->toBe(10)
        ->and($service->fresh()->content_version)->toBe(3)->and(Revision::query()->count())->toBe(3);
    $other = app(SaveServiceDraft::class)->handle(array_replace(serviceDraftFixture(), ['path' => '/another-service']));
    $this->post(route('admin.services.revision', [$other, $revision]), ['content_version' => 1])->assertNotFound();
    $pageRevision = Revision::query()->create(['revisionable_type' => Page::class, 'revisionable_id' => $service->id, 'snapshot' => $revision->snapshot]);
    $this->post(route('admin.services.revision', [$service, $pageRevision]), ['content_version' => 3])->assertNotFound();
});

it('locks ready featured and section images and retains archived and revision references', function () {
    $image = Media::query()->create(['disk' => 'public', 'path' => 'media/11111111-1111-4111-8111-111111111111/original.png', 'original_name' => 'fixture.png',
        'mime' => 'image/png', 'size' => 100, 'alt' => 'Synthetic image', 'sha256' => hash('sha256', 'fixture'), 'processing_status' => 'ready']);
    $service = app(SaveServiceDraft::class)->handle(serviceDraftFixture() + ['media_id' => $image->id]);
    expect(fn () => app(ArchiveMedia::class)->handle($image))->toThrow(ValidationException::class);
    app(SaveServiceDraft::class)->handle(serviceDraftFixture() + ['content_version' => 1], $service);
    expect(fn () => app(ArchiveMedia::class)->handle($image))->toThrow(ValidationException::class);
    $image->update(['alt' => '']);
    $this->post(route('admin.services.store'), array_replace(serviceDraftFixture(), ['path' => '/bad-image', 'media_id' => $image->id]))->assertSessionHasErrors();
    expect(Service::query()->count())->toBe(1);
});

it('archives and restores drafts with version checks and protects published and imported services', function () {
    $service = app(SaveServiceDraft::class)->handle(serviceDraftFixture());
    $this->delete(route('admin.services.archive', $service), ['content_version' => 9])->assertSessionHasErrors('content_version');
    $this->delete(route('admin.services.archive', $service), ['content_version' => 1])->assertSessionHasNoErrors();
    $this->get(route('admin.services.edit', $service))->assertOk()->assertSee('Restore service draft');
    $this->post(route('admin.services.restore', $service), ['content_version' => 2])->assertSessionHasNoErrors();
    $service = $service->fresh();
    expect($service->trashed())->toBeFalse()->and($service->content_version)->toBe(3);
    $service->update(['legacy_wp_id' => 123]);
    $this->put(route('admin.services.update', $service), array_replace(serviceDraftFixture(), ['path' => '/renamed', 'content_version' => 3]))->assertSessionHasErrors('path');
    $this->delete(route('admin.services.archive', $service), ['content_version' => 3])->assertForbidden();
    $service->update(['status' => 'published']);
    $this->put(route('admin.services.update', $service), serviceDraftFixture() + ['content_version' => 3])->assertForbidden();
    $this->get(route('admin.services.edit', $service))->assertOk()->assertDontSee('Save service draft');
});

it('orders and filters services with a stable ID tie-break', function () {
    $first = app(SaveServiceDraft::class)->handle(serviceDraftFixture());
    app(SaveServiceDraft::class)->handle(array_replace(serviceDraftFixture(), ['title' => 'Earlier fixture', 'path' => '/earlier', 'sort' => 1]));
    $this->get(route('admin.services.index'))->assertOk()->assertSeeInOrder(['Earlier fixture', $first->title]);
    $this->get(route('admin.services.index', ['q' => 'Earlier']))->assertSee('Earlier fixture')->assertDontSee('Synthetic service');
});

it('allows SEO managers read-only access and enforces CSRF and authenticated sessions', function () {
    $service = app(SaveServiceDraft::class)->handle(serviceDraftFixture());
    $user = adminAccount('SEO Manager');
    $this->actingAs($user)->withSession(verifiedAdminSession($user));
    $this->get(route('admin.services.index'))->assertOk()->assertDontSee('Create service draft');
    $this->get(route('admin.services.edit', $service))->assertOk()->assertDontSee('Save service draft');
    $this->get(route('admin.services.create'))->assertForbidden();
    $this->post(route('admin.services.store'), serviceDraftFixture())->assertForbidden();
    $this->put(route('admin.services.update', $service), serviceDraftFixture() + ['content_version' => 1])->assertForbidden();
    $this->delete(route('admin.services.archive', $service), ['content_version' => 1])->assertForbidden();
    $this->post(route('admin.services.revision', [$service, Revision::query()->sole()]), ['content_version' => 1])->assertForbidden();
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
    $this->app['env'] = 'local';
    $this->post(route('admin.services.store'), serviceDraftFixture())->assertStatus(419);
    auth()->logout();
    $this->flushSession();
    $this->get(route('admin.services.index'))->assertRedirect(route('admin.login'));
});
