<?php

use App\Actions\Admin\ArchiveMedia;
use App\Actions\Admin\SavePageDraft;
use App\Actions\Admin\SavePortfolioDraft;
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
function portfolioDraftFixture(): array
{
    return ['title' => 'Synthetic case study', 'path' => '/Synthetic-case-study/', 'sort' => 10,
        'blocks' => [['type' => 'heading', 'text' => 'Case-study heading'], ['type' => 'paragraph', 'text' => 'Synthetic case-study copy.']]];
}

it('creates exact-path portfolio drafts and whitelists content fields', function () {
    $this->post(route('admin.portfolio.store'), portfolioDraftFixture() + ['status' => 'published', 'legacy_wp_id' => 123, 'body' => '<script>bad()</script>',
        'details' => ['injected' => true], 'seo_title' => 'Injected SEO', 'published_at' => now()])->assertSessionHasNoErrors()->assertRedirect();
    $item = PortfolioItem::query()->sole();
    expect($item->status)->toBe('draft')->and($item->path)->toBe('/Synthetic-case-study/')->and($item->published_at)->toBeNull()
        ->and($item->legacy_wp_id)->toBeNull()->and($item->getAttribute('body'))->toBeNull()->and($item->getAttribute('seo_title'))->toBeNull()
        ->and($item->content_version)->toBe(1)->and(Revision::query()->sole()->revisionable_type)->toBe(PortfolioItem::class);
    $this->get(route('admin.portfolio.edit', $item))->assertOk()->assertSee('Case-study sections')->assertSee('Case-study heading');
    $this->get('/Synthetic-case-study/')->assertNotFound();
});

it('rejects unsafe sections malformed JSON invalid ordering and reserved paths', function (array $values, string $field) {
    $this->post(route('admin.portfolio.store'), array_replace(portfolioDraftFixture(), $values))->assertSessionHasErrors($field);
    expect(PortfolioItem::query()->count())->toBe(0)->and(Revision::query()->count())->toBe(0);
})->with([
    [['blocks_json' => '{bad'], 'blocks'], [['blocks' => [['type' => 'paragraph', 'text' => '<script>bad()</script>']]], 'blocks.0.text'],
    [['blocks' => [['type' => 'html', 'text' => 'bad']]], 'blocks.0.type'], [['blocks' => [['type' => 'paragraph', 'text' => 'ok', 'script' => 'bad']]], 'blocks.0'],
    [['path' => '/'], 'path'], [['path' => '/wp-admin'], 'path'], [['sort' => -1], 'sort'], [['media_id' => ['malformed']], 'media_id'],
]);

it('saves versioned drafts without rewriting source metadata and rejects stale updates', function () {
    $item = app(SavePortfolioDraft::class)->handle(portfolioDraftFixture());
    $item->update(['body' => 'Source body', 'details' => ['source' => true], 'seo_title' => 'Source SEO']);
    $this->put(route('admin.portfolio.update', $item), array_replace(portfolioDraftFixture(), ['title' => 'Changed', 'sort' => 2, 'content_version' => 1]))->assertSessionHasNoErrors();
    $this->put(route('admin.portfolio.update', $item), portfolioDraftFixture() + ['content_version' => 1])->assertSessionHasErrors('content_version');
    expect($item->fresh()->title)->toBe('Changed')->and($item->fresh()->getAttribute('body'))->toBe('Source body')
        ->and($item->fresh()->getAttribute('seo_title'))->toBe('Source SEO')->and($item->fresh()->content_version)->toBe(2)->and(Revision::query()->count())->toBe(2);
});

it('reserves cross-module and archived aliases without confusing matching IDs', function () {
    $page = Page::query()->create(['title' => 'Page fixture', 'path' => '/occupied-page']);
    $service = Service::query()->create(['title' => 'Service fixture', 'path' => '/occupied-service']);
    $item = app(SavePortfolioDraft::class)->handle(portfolioDraftFixture());
    expect($page->id)->toBe($item->id)->and($service->id)->toBe($item->id);
    foreach (['/OCCUPIED-page/', '/occupied-service/'] as $path) {
        $this->put(route('admin.portfolio.update', $item), array_replace(portfolioDraftFixture(), ['path' => $path, 'content_version' => 1]))->assertSessionHasErrors('path');
    }
    expect(fn () => app(SavePageDraft::class)->handle(['title' => 'Collision', 'path' => '/synthetic-case-study', 'blocks' => []]))->toThrow(ValidationException::class);
    expect(fn () => app(SaveServiceDraft::class)->handle(['title' => 'Collision', 'path' => '/synthetic-case-study/', 'sort' => 0, 'blocks' => []]))->toThrow(ValidationException::class);
    $this->delete(route('admin.portfolio.archive', $item), ['content_version' => 1])->assertSessionHasNoErrors();
    $this->post(route('admin.portfolio.store'), portfolioDraftFixture())->assertSessionHasErrors('path');
});

it('restores only the matching content revision retaining the current path and SEO', function () {
    $item = app(SavePortfolioDraft::class)->handle(portfolioDraftFixture());
    $revision = Revision::query()->sole();
    $item->update(['seo_title' => 'Existing SEO']);
    $item = app(SavePortfolioDraft::class)->handle(array_replace(portfolioDraftFixture(), ['title' => 'Changed', 'path' => '/current-path', 'sort' => 1, 'content_version' => 1]), $item);
    $this->post(route('admin.portfolio.revision', [$item, $revision]), ['content_version' => 2])->assertSessionHasNoErrors();
    expect($item->fresh()->title)->toBe('Synthetic case study')->and($item->fresh()->path)->toBe('/current-path')->and($item->fresh()->sort)->toBe(10)
        ->and($item->fresh()->getAttribute('seo_title'))->toBe('Existing SEO')->and($item->fresh()->content_version)->toBe(3);
    $other = app(SavePortfolioDraft::class)->handle(array_replace(portfolioDraftFixture(), ['path' => '/another-case-study']));
    $this->post(route('admin.portfolio.revision', [$other, $revision]), ['content_version' => 1])->assertNotFound();
    $service = Service::query()->create(['title' => 'Service fixture', 'path' => '/service-fixture']);
    expect($service->id)->toBe($item->id);
    $wrongModel = Revision::query()->create(['revisionable_type' => Service::class, 'revisionable_id' => $service->id, 'snapshot' => $revision->snapshot]);
    $this->post(route('admin.portfolio.revision', [$item, $wrongModel]), ['content_version' => 3])->assertNotFound();
    $this->post(route('admin.services.revision', [$service, $revision]), ['content_version' => 1])->assertNotFound();
});

it('protects featured image revision references and rechecks invalid images on save', function () {
    $image = Media::query()->create(['disk' => 'public', 'path' => 'media/11111111-1111-4111-8111-111111111111/original.png', 'original_name' => 'fixture.png',
        'mime' => 'image/png', 'size' => 100, 'alt' => 'Synthetic case-study image', 'sha256' => hash('sha256', 'fixture'), 'processing_status' => 'ready']);
    $item = app(SavePortfolioDraft::class)->handle(portfolioDraftFixture() + ['media_id' => $image->id]);
    expect(fn () => app(ArchiveMedia::class)->handle($image))->toThrow(ValidationException::class);
    app(SavePortfolioDraft::class)->handle(portfolioDraftFixture() + ['content_version' => 1], $item);
    expect(fn () => app(ArchiveMedia::class)->handle($image))->toThrow(ValidationException::class);
    $image->update(['alt' => '']);
    $this->post(route('admin.portfolio.store'), array_replace(portfolioDraftFixture(), ['path' => '/bad-image', 'media_id' => $image->id]))->assertSessionHasErrors();
    expect(PortfolioItem::query()->count())->toBe(1);
});

it('archives restores and preserves imported or previously published entries', function () {
    $item = app(SavePortfolioDraft::class)->handle(portfolioDraftFixture());
    $this->delete(route('admin.portfolio.archive', $item), ['content_version' => 99])->assertSessionHasErrors('content_version');
    $this->delete(route('admin.portfolio.archive', $item), ['content_version' => 1])->assertSessionHasNoErrors();
    $this->get(route('admin.portfolio.edit', $item))->assertOk()->assertSee('Restore portfolio draft');
    $this->post(route('admin.portfolio.restore', $item), ['content_version' => 2])->assertSessionHasNoErrors();
    expect($item->fresh()->content_version)->toBe(3)->and($item->fresh()->trashed())->toBeFalse();
    $item->update(['legacy_wp_id' => 123]);
    $this->put(route('admin.portfolio.update', $item), array_replace(portfolioDraftFixture(), ['path' => '/renamed', 'content_version' => 3]))->assertSessionHasErrors('path');
    $this->delete(route('admin.portfolio.archive', $item), ['content_version' => 3])->assertForbidden();
    $item->update(['status' => 'published']);
    $this->put(route('admin.portfolio.update', $item), portfolioDraftFixture() + ['content_version' => 3])->assertSessionHasNoErrors();
    $this->get(route('admin.portfolio.edit', $item))->assertOk()->assertSee('Save published portfolio');
});

it('orders and filters portfolio items and keeps SEO managers read-only', function () {
    $item = app(SavePortfolioDraft::class)->handle(portfolioDraftFixture());
    app(SavePortfolioDraft::class)->handle(array_replace(portfolioDraftFixture(), ['title' => 'Earlier entry', 'path' => '/earlier-entry', 'sort' => 1]));
    $this->get(route('admin.portfolio.index'))->assertSeeInOrder(['Earlier entry', $item->title]);
    $this->get(route('admin.portfolio.index', ['q' => 'Earlier']))->assertSee('Earlier entry')->assertDontSee('Synthetic case study');
    $user = adminAccount('SEO Manager');
    $this->actingAs($user)->withSession(verifiedAdminSession($user));
    $this->get(route('admin.portfolio.index'))->assertOk()->assertDontSee('Create portfolio draft');
    $this->get(route('admin.portfolio.edit', $item))->assertOk()->assertDontSee('Save portfolio draft');
    $this->post(route('admin.portfolio.store'), portfolioDraftFixture())->assertForbidden();
    $this->put(route('admin.portfolio.update', $item), portfolioDraftFixture() + ['content_version' => 1])->assertForbidden();
    $this->delete(route('admin.portfolio.archive', $item), ['content_version' => 1])->assertForbidden();
});

it('requires authentication and CSRF before writing portfolio content', function () {
    $this->app['env'] = 'local';
    $this->post(route('admin.portfolio.store'), portfolioDraftFixture())->assertStatus(419);
    auth()->logout();
    $this->flushSession();
    $this->get(route('admin.portfolio.index'))->assertRedirect(route('admin.login'));
    expect(PortfolioItem::query()->count())->toBe(0);
});
