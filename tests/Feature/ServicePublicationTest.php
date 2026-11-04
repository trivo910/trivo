<?php

use App\Actions\Admin\ChangePagePublication;
use App\Actions\Admin\ChangeServicePublication;
use App\Actions\Admin\SaveServiceDraft;
use App\Models\Media;
use App\Models\Page;
use App\Models\Redirect;
use App\Models\Revision;
use App\Models\Service;
use App\Services\Content\PublishDuePages;
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
    $this->service = Service::query()->create(['title' => 'Synthetic service', 'path' => '/service-publication', 'status' => 'draft', 'sort' => 7,
        'body' => 'Source body', 'blocks' => [['type' => 'paragraph', 'text' => 'Synthetic source copy']],
        'seo_title' => 'Service SEO', 'seo_description' => 'Service description', 'robots' => 'index,follow', 'schema_type' => 'Service']);
});

it('publishes a ready service without changing its content identity or ordering', function () {
    $this->post(route('admin.services.publication', $this->service), ['operation' => 'publish', 'content_version' => 1, 'path' => '/injected'])->assertSessionHasNoErrors();
    $service = $this->service->fresh();
    expect($service->status)->toBe('published')->and($service->published_at)->not->toBeNull()->and($service->publication_locked_at)->not->toBeNull()
        ->and($service->path)->toBe('/service-publication')->and($service->body)->toBe('Source body')->and($service->sort)->toBe(7)->and($service->content_version)->toBe(2)
        ->and(Revision::query()->sole()->revisionable_type)->toBe(Service::class)->and(Revision::query()->sole()->snapshot['event'])->toBe('service_publish');
    $this->get(route('admin.services.edit', $service))->assertOk()->assertDontSee('Save service draft')->assertDontSee('Restore service revision #');
    $this->post(route('admin.services.publication', $service), ['operation' => 'cancel', 'content_version' => 2])->assertForbidden();
    $this->get('/service-publication')->assertNotFound();
});

it('schedules and cancels with exact UTC timing while retaining path and archive protection', function () {
    $date = now()->addDay()->format('Y-m-d\TH:i');
    $this->post(route('admin.services.publication', $this->service), ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => $date])->assertSessionHasNoErrors();
    expect($this->service->fresh()->published_at->format('Y-m-d\TH:i:s'))->toBe($date.':00');
    $this->post(route('admin.services.publication', $this->service), ['operation' => 'cancel', 'content_version' => 2])->assertSessionHasNoErrors();
    $service = $this->service->fresh();
    expect($service->status)->toBe('draft')->and($service->published_at)->toBeNull()->and($service->publication_locked_at)->not->toBeNull();
    expect(fn () => app(SaveServiceDraft::class)->handle(['title' => 'Changed', 'path' => '/renamed', 'blocks' => $service->blocks, 'sort' => 7, 'content_version' => 3], $service))->toThrow(ValidationException::class);
    $this->delete(route('admin.services.archive', $service), ['content_version' => 3])->assertForbidden();
    $this->put(route('admin.services.update', $service), ['title' => 'Edited draft', 'path' => $service->path, 'blocks' => $service->blocks, 'sort' => 7, 'content_version' => 3])->assertSessionHasNoErrors();
});

it('rejects unsafe readiness and stale versions atomically', function (string $fault) {
    match ($fault) {
        'metadata' => $this->service->update(['seo_title' => null]),
        'sections' => $this->service->update(['blocks' => []]),
        'schema' => $this->service->update(['schema_type' => 'AboutPage']),
        'conflict' => Redirect::query()->create(['from_path' => '/service-publication', 'to_url' => '/', 'status_code' => 301]),
        'flags' => $this->service->update(['noindex' => true]),
        'stale' => null,
    };
    $this->post(route('admin.services.publication', $this->service), ['operation' => 'publish', 'content_version' => $fault === 'stale' ? 99 : 1])->assertSessionHasErrors();
    expect($this->service->fresh()->status)->toBe('draft')->and($this->service->fresh()->publication_locked_at)->toBeNull()->and(Revision::query()->count())->toBe(0);
})->with(['metadata', 'sections', 'schema', 'conflict', 'flags', 'stale']);

it('validates future UTC schedules through the service form request', function (string $date) {
    $this->post(route('admin.services.publication', $this->service), ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => $date])->assertSessionHasErrors('scheduled_for');
    expect($this->service->fresh()->status)->toBe('draft');
})->with(['2020-01-01T00:00', '2030-02-30T12:00', 'not-a-date']);

it('publishes only the intended model once when due and preserves the scheduled timestamp', function () {
    $date = now()->addHour()->format('Y-m-d\TH:i');
    app(ChangeServicePublication::class)->handle($this->service, ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => $date]);
    $page = Page::query()->create(['title' => 'Synthetic page', 'status' => 'draft', 'path' => '/publication-page', 'blocks' => [['type' => 'paragraph', 'text' => 'Copy']],
        'seo_title' => 'Page SEO', 'seo_description' => 'Page description', 'robots' => 'index,follow']);
    expect($page->id)->toBe($this->service->id);
    app(ChangePagePublication::class)->handle($page, ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => $date]);
    expect(app(PublishDueServices::class)->handle())->toBe(['published' => 0, 'blocked' => 0]);
    $this->travel(2)->hours();
    expect(app(PublishDueServices::class)->handle())->toBe(['published' => 1, 'blocked' => 0]);
    expect($this->service->fresh()->published_at->format('Y-m-d\TH:i:s'))->toBe($date.':00')->and($page->fresh()->status)->toBe('scheduled');
    expect(app(PublishDueServices::class)->handle())->toBe(['published' => 0, 'blocked' => 0]);
    expect(Activity::query()->where('event', 'service_scheduled_published')->sole()->causer_id)->toBeNull();
    expect(app(PublishDuePages::class)->handle())->toBe(['published' => 1, 'blocked' => 0]);
});

it('rechecks featured image readiness at due time suppresses repeated errors and permits repair', function () {
    $image = Media::query()->create(['disk' => 'public', 'path' => 'media/fixture/original.png', 'original_name' => 'fixture.png', 'mime' => 'image/png',
        'size' => 100, 'sha256' => hash('sha256', 'fixture'), 'alt' => 'Synthetic featured image', 'processing_status' => 'ready']);
    $this->service->update(['media_id' => $image->id]);
    app(ChangeServicePublication::class)->handle($this->service, ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => now()->addHour()->format('Y-m-d\TH:i')]);
    $image->update(['processing_status' => 'failed']);
    $this->travel(2)->hours();
    expect(app(PublishDueServices::class)->handle())->toBe(['published' => 0, 'blocked' => 1])->and($this->service->fresh()->publication_error)->toBe('validation_failed');
    $version = $this->service->fresh()->content_version;
    expect(app(PublishDueServices::class)->handle())->toBe(['published' => 0, 'blocked' => 1])->and($this->service->fresh()->content_version)->toBe($version)->and(Revision::query()->count())->toBe(2);
    $this->withSession(verifiedAdminSession($this->admin))->get(route('admin.services.edit', $this->service))->assertSee('Scheduled publication is blocked');
    $image->update(['processing_status' => 'ready']);
    expect(app(PublishDueServices::class)->handle())->toBe(['published' => 1, 'blocked' => 0])->and($this->service->fresh()->publication_error)->toBeNull();
});

it('enforces service publication permission CSRF archive boundaries and revision kinds', function () {
    app(ChangeServicePublication::class)->handle($this->service, ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => now()->addHour()->format('Y-m-d\TH:i')]);
    $revision = Revision::query()->sole();
    $this->post(route('admin.services.publication', $this->service), ['operation' => 'cancel', 'content_version' => 2])->assertSessionHasNoErrors();
    $this->post(route('admin.services.revision', [$this->service, $revision]), ['content_version' => 3])->assertNotFound();
    $user = adminAccount('SEO Manager');
    $this->actingAs($user)->withSession(verifiedAdminSession($user));
    $this->post(route('admin.services.publication', $this->service), ['operation' => 'publish', 'content_version' => 3])->assertForbidden();
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
    $this->app['env'] = 'local';
    $this->post(route('admin.services.publication', $this->service), ['operation' => 'publish', 'content_version' => 3])->assertStatus(419);
    $this->app['env'] = 'testing';
    $this->service->delete();
    $this->post(route('admin.services.publication', $this->service), ['operation' => 'publish', 'content_version' => 3])->assertNotFound();
    auth()->logout();
    $this->flushSession();
    $this->get(route('admin.services.edit', $this->service))->assertRedirect(route('admin.login'));
});

it('bounds service cron runs respects the shared lock and rejects invalid limits', function () {
    $second = $this->service->replicate()->fill(['path' => '/second-service']);
    $second->save();
    foreach ([$this->service, $second] as $service) {
        app(ChangeServicePublication::class)->handle($service, ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => now()->addHour()->format('Y-m-d\TH:i')]);
    }
    $this->travel(2)->hours();
    $lock = Cache::lock('content-path-writes', 120);
    $lock->get();
    expect(app(PublishDueServices::class)->handle(1))->toBe(['published' => 0, 'blocked' => 0]);
    $lock->release();
    $this->artisan('trivo:publish-services', ['--limit' => '1'])->expectsOutput('Published: 1. Blocked: 0.')->assertSuccessful();
    expect(Service::query()->where('status', 'scheduled')->count())->toBe(1);
    $this->artisan('trivo:publish-services', ['--limit' => '101'])->assertExitCode(2);
});
