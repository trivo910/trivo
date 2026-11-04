<?php

use App\Actions\Admin\ChangePagePublication;
use App\Actions\Admin\ChangePortfolioPublication;
use App\Actions\Admin\ChangeServicePublication;
use App\Actions\Admin\SavePortfolioDraft;
use App\Models\Media;
use App\Models\Page;
use App\Models\PortfolioItem;
use App\Models\Redirect;
use App\Models\Revision;
use App\Models\Service;
use App\Services\Content\PublishDuePages;
use App\Services\Content\PublishDuePortfolio;
use App\Services\Content\PublishDueServices;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->seed(RolePermissionSeeder::class);
    $this->admin = adminAccount('Editor');
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
    $this->item = PortfolioItem::query()->create(['title' => 'Synthetic case study', 'path' => '/case-publication', 'status' => 'draft', 'sort' => 7,
        'body' => 'Source body', 'details' => ['source' => true], 'blocks' => [['type' => 'paragraph', 'text' => 'Synthetic case-study copy']],
        'seo_title' => 'Case-study SEO', 'seo_description' => 'Case-study description', 'robots' => 'index,follow', 'schema_type' => 'CreativeWork']);
});

it('publishes a ready case study without changing its source fields or canonical path', function () {
    $this->post(route('admin.portfolio.publication', $this->item), ['operation' => 'publish', 'content_version' => 1, 'path' => '/injected'])->assertSessionHasNoErrors();
    $item = $this->item->fresh();
    expect($item->status)->toBe('published')->and($item->published_at)->not->toBeNull()->and($item->publication_locked_at)->not->toBeNull()
        ->and($item->path)->toBe('/case-publication')->and($item->getAttribute('body'))->toBe('Source body')->and($item->details)->toBe(['source' => true])
        ->and($item->sort)->toBe(7)->and($item->content_version)->toBe(2)->and(Revision::query()->sole()->revisionable_type)->toBe(PortfolioItem::class)
        ->and(Revision::query()->sole()->snapshot['event'])->toBe('portfolio_publish');
    $this->get(route('admin.portfolio.edit', $item))->assertOk()->assertDontSee('Save portfolio draft')->assertDontSee('Restore portfolio revision #');
    $this->post(route('admin.portfolio.publication', $item), ['operation' => 'cancel', 'content_version' => 2])->assertForbidden();
    $this->get('/case-publication')->assertNotFound();
});

it('schedules and cancels exact UTC minutes with permanent path protection', function () {
    $date = now()->addDay()->format('Y-m-d\TH:i');
    $this->post(route('admin.portfolio.publication', $this->item), ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => $date])->assertSessionHasNoErrors();
    expect($this->item->fresh()->published_at->format('Y-m-d\TH:i:s'))->toBe($date.':00');
    $this->post(route('admin.portfolio.publication', $this->item), ['operation' => 'cancel', 'content_version' => 2])->assertSessionHasNoErrors();
    $item = $this->item->fresh();
    expect($item->status)->toBe('draft')->and($item->published_at)->toBeNull()->and($item->publication_locked_at)->not->toBeNull();
    expect(fn () => app(SavePortfolioDraft::class)->handle(['title' => 'Changed', 'path' => '/renamed', 'blocks' => $item->blocks, 'sort' => 7, 'content_version' => 3], $item))->toThrow(ValidationException::class);
    $this->delete(route('admin.portfolio.archive', $item), ['content_version' => 3])->assertForbidden();
    $this->put(route('admin.portfolio.update', $item), ['title' => 'Edited draft', 'path' => $item->path, 'blocks' => $item->blocks, 'sort' => 7, 'content_version' => 3])->assertSessionHasNoErrors();
});

it('rejects invalid readiness and stale transitions without partial publication', function (string $fault) {
    match ($fault) {
        'metadata' => $this->item->update(['seo_title' => null]),
        'sections' => $this->item->update(['blocks' => []]),
        'schema' => $this->item->update(['schema_type' => 'Service']),
        'conflict' => Redirect::query()->create(['from_path' => '/case-publication', 'to_url' => '/', 'status_code' => 301]),
        'flags' => $this->item->update(['noindex' => true]),
        'stale' => null,
    };
    $this->post(route('admin.portfolio.publication', $this->item), ['operation' => 'publish', 'content_version' => $fault === 'stale' ? 99 : 1])->assertSessionHasErrors();
    expect($this->item->fresh()->status)->toBe('draft')->and($this->item->fresh()->publication_locked_at)->toBeNull()->and(Revision::query()->count())->toBe(0);
})->with(['metadata', 'sections', 'schema', 'conflict', 'flags', 'stale']);

it('requires a valid future UTC scheduling input', function (string $date) {
    $this->post(route('admin.portfolio.publication', $this->item), ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => $date])->assertSessionHasErrors('scheduled_for');
    expect($this->item->fresh()->status)->toBe('draft');
})->with(['2020-01-01T00:00', '2030-02-30T12:00', 'not-a-date']);

it('publishes only portfolio schedules once while preserving timestamps and model isolation', function () {
    $date = now()->addHour()->format('Y-m-d\TH:i');
    app(ChangePortfolioPublication::class)->handle($this->item, ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => $date]);
    $data = ['title' => 'Other fixture', 'status' => 'draft', 'blocks' => [['type' => 'paragraph', 'text' => 'Copy']], 'seo_title' => 'SEO', 'seo_description' => 'Description', 'robots' => 'index,follow'];
    $page = Page::query()->create($data + ['path' => '/other-page']);
    $service = Service::query()->create($data + ['path' => '/other-service']);
    expect($page->id)->toBe($this->item->id)->and($service->id)->toBe($this->item->id);
    app(ChangePagePublication::class)->handle($page, ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => $date]);
    app(ChangeServicePublication::class)->handle($service, ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => $date]);
    expect(app(PublishDuePortfolio::class)->handle())->toBe(['published' => 0, 'blocked' => 0]);
    $this->travel(2)->hours();
    expect(app(PublishDuePortfolio::class)->handle())->toBe(['published' => 1, 'blocked' => 0]);
    expect($this->item->fresh()->published_at->format('Y-m-d\TH:i:s'))->toBe($date.':00')->and($page->fresh()->status)->toBe('scheduled')->and($service->fresh()->status)->toBe('scheduled');
    expect(app(PublishDuePortfolio::class)->handle())->toBe(['published' => 0, 'blocked' => 0]);
    expect(Activity::query()->where('event', 'portfolio_scheduled_published')->sole()->causer_id)->toBeNull();
    expect(app(PublishDuePages::class)->handle())->toBe(['published' => 1, 'blocked' => 0]);
    expect(app(PublishDueServices::class)->handle())->toBe(['published' => 1, 'blocked' => 0]);
});

it('rechecks featured images at due time records one failure and retries after repair', function () {
    $image = Media::query()->create(['disk' => 'public', 'path' => 'media/fixture/original.png', 'original_name' => 'fixture.png', 'mime' => 'image/png', 'size' => 100,
        'sha256' => hash('sha256', 'fixture'), 'alt' => 'Synthetic case-study image', 'processing_status' => 'ready']);
    $this->item->update(['media_id' => $image->id]);
    app(ChangePortfolioPublication::class)->handle($this->item, ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => now()->addHour()->format('Y-m-d\TH:i')]);
    $image->update(['alt' => '']);
    $this->travel(2)->hours();
    expect(app(PublishDuePortfolio::class)->handle())->toBe(['published' => 0, 'blocked' => 1])->and($this->item->fresh()->publication_error)->toBe('validation_failed');
    $version = $this->item->fresh()->content_version;
    expect(app(PublishDuePortfolio::class)->handle())->toBe(['published' => 0, 'blocked' => 1])->and($this->item->fresh()->content_version)->toBe($version)->and(Revision::query()->count())->toBe(2);
    $this->withSession(verifiedAdminSession($this->admin))->get(route('admin.portfolio.edit', $this->item))->assertSee('Scheduled publication is blocked');
    $image->update(['alt' => 'Repaired alt']);
    expect(app(PublishDuePortfolio::class)->handle())->toBe(['published' => 1, 'blocked' => 0])->and($this->item->fresh()->publication_error)->toBeNull();
});

it('enforces publisher permissions CSRF archive boundaries and revision kind protection', function () {
    app(ChangePortfolioPublication::class)->handle($this->item, ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => now()->addHour()->format('Y-m-d\TH:i')]);
    $revision = Revision::query()->sole();
    $this->post(route('admin.portfolio.publication', $this->item), ['operation' => 'cancel', 'content_version' => 2])->assertSessionHasNoErrors();
    $this->post(route('admin.portfolio.revision', [$this->item, $revision]), ['content_version' => 3])->assertNotFound();
    $user = adminAccount('SEO Manager');
    $this->actingAs($user)->withSession(verifiedAdminSession($user));
    $this->post(route('admin.portfolio.publication', $this->item), ['operation' => 'publish', 'content_version' => 3])->assertForbidden();
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
    $this->app['env'] = 'local';
    $this->post(route('admin.portfolio.publication', $this->item), ['operation' => 'publish', 'content_version' => 3])->assertStatus(419);
    $this->app['env'] = 'testing';
    $this->item->delete();
    $this->post(route('admin.portfolio.publication', $this->item), ['operation' => 'publish', 'content_version' => 3])->assertNotFound();
    auth()->logout();
    $this->flushSession();
    $this->get(route('admin.portfolio.edit', $this->item))->assertRedirect(route('admin.login'));
});

it('bounds portfolio cron batches respects shared locks and rejects invalid limits', function () {
    $second = $this->item->replicate()->fill(['path' => '/second-case-study']);
    $second->save();
    foreach ([$this->item, $second] as $item) {
        app(ChangePortfolioPublication::class)->handle($item, ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => now()->addHour()->format('Y-m-d\TH:i')]);
    }
    $this->travel(2)->hours();
    $lock = Cache::lock('content-path-writes', 120);
    $lock->get();
    expect(app(PublishDuePortfolio::class)->handle(1))->toBe(['published' => 0, 'blocked' => 0]);
    $lock->release();
    $this->artisan('trivo:publish-portfolio', ['--limit' => '1'])->expectsOutput('Published: 1. Blocked: 0.')->assertSuccessful();
    expect(PortfolioItem::query()->where('status', 'scheduled')->count())->toBe(1);
    $this->artisan('trivo:publish-portfolio', ['--limit' => '101'])->assertExitCode(2);
});
