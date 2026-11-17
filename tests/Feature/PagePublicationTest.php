<?php

use App\Actions\Admin\ChangePagePublication;
use App\Actions\Admin\SavePageDraft;
use App\Models\Media;
use App\Models\Page;
use App\Models\Redirect;
use App\Models\Revision;
use App\Services\Content\PublishDuePages;
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
    $this->page = Page::query()->create(['title' => 'Synthetic page', 'path' => '/publication-fixture', 'status' => 'draft',
        'blocks' => [['type' => 'paragraph', 'text' => 'Synthetic copy']], 'seo_title' => 'Synthetic SEO title', 'seo_description' => 'Synthetic description', 'robots' => 'index,follow']);
});

it('publishes valid content without modifying its path content or metadata', function () {
    $this->post(route('admin.pages.publication', $this->page), ['operation' => 'publish', 'content_version' => 1, 'path' => '/injected'])->assertSessionHasNoErrors()->assertRedirect();
    $page = $this->page->fresh();
    expect($page->status)->toBe('published')->and($page->path)->toBe('/publication-fixture')->and($page->blocks)->toHaveCount(1)
        ->and($page->seo_title)->toBe('Synthetic SEO title')->and($page->published_at)->not->toBeNull()->and($page->publication_locked_at)->not->toBeNull()
        ->and($page->content_version)->toBe(2)->and(Revision::query()->sole()->snapshot['kind'])->toBe('publication');
    $this->get(route('admin.pages.edit', $page))->assertOk()->assertSee('Save published page')->assertDontSee('Restore revision #');
    $this->post(route('admin.pages.publication', $page), ['operation' => 'publish', 'content_version' => 2])->assertForbidden();
    $this->get('/publication-fixture')->assertOk()->assertSee('Synthetic copy');
    $this->put(route('admin.pages.update', $page), ['title' => 'Updated page', 'path' => $page->path,
        'blocks' => [['type' => 'paragraph', 'text' => 'Updated live copy']], 'content_version' => 2])->assertSessionHasNoErrors();
    $this->get('/publication-fixture')->assertOk()->assertSee('Updated live copy')->assertDontSee('Synthetic copy');
});

it('schedules exact UTC minutes cancels and keeps the canonical path locked', function () {
    $date = now()->addDay()->format('Y-m-d\TH:i');
    $this->post(route('admin.pages.publication', $this->page), ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => $date])->assertSessionHasNoErrors();
    expect($this->page->fresh()->status)->toBe('scheduled')->and($this->page->fresh()->published_at->format('Y-m-d\TH:i:s'))->toBe($date.':00');
    $this->post(route('admin.pages.publication', $this->page), ['operation' => 'cancel', 'content_version' => 2])->assertSessionHasNoErrors();
    $page = $this->page->fresh();
    expect($page->status)->toBe('draft')->and($page->published_at)->toBeNull()->and($page->publication_locked_at)->not->toBeNull();
    expect(fn () => app(SavePageDraft::class)->handle(['title' => $page->title, 'path' => '/changed', 'blocks' => $page->blocks, 'content_version' => 3], $page))->toThrow(ValidationException::class);
    $this->delete(route('admin.pages.archive', $page), ['content_version' => 3])->assertForbidden();
    app(SavePageDraft::class)->handle(['title' => 'Edited after cancellation', 'path' => $page->path, 'blocks' => $page->blocks, 'content_version' => 3], $page);
    expect($page->fresh()->title)->toBe('Edited after cancellation');
});

it('rejects invalid schedules and stale transitions', function (string $date) {
    $this->post(route('admin.pages.publication', $this->page), ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => $date])->assertSessionHasErrors('scheduled_for');
    expect($this->page->fresh()->status)->toBe('draft')->and(Revision::query()->count())->toBe(0);
})->with(['2020-01-01T12:00', 'not-a-date', '2030-02-30T12:00', '2030-01-01T12:00+05:30']);

it('rejects missing metadata empty content and changed path conflicts atomically', function (string $fault) {
    match ($fault) {
        'title' => $this->page->update(['seo_title' => null]),
        'description' => $this->page->update(['seo_description' => null]),
        'empty' => $this->page->update(['blocks' => []]),
        'conflict' => Redirect::query()->create(['from_path' => '/publication-fixture', 'to_url' => '/', 'status_code' => 301]),
        'flags' => $this->page->update(['noindex' => true]),
        'html' => $this->page->update(['blocks' => [['type' => 'paragraph', 'text' => '<script>bad()</script>']]]),
    };
    $this->post(route('admin.pages.publication', $this->page), ['operation' => 'publish', 'content_version' => 1])->assertSessionHasErrors();
    expect($this->page->fresh()->status)->toBe('draft')->and($this->page->fresh()->publication_locked_at)->toBeNull()->and(Revision::query()->count())->toBe(0);
})->with(['title', 'description', 'empty', 'conflict', 'flags', 'html']);

it('publishes due schedules once and retains their intended timestamp', function () {
    app(ChangePagePublication::class)->handle($this->page, ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => now()->addHour()->format('Y-m-d\TH:i')]);
    expect(app(PublishDuePages::class)->handle())->toBe(['published' => 0, 'blocked' => 0]);
    $intended = $this->page->fresh()->published_at->toIso8601String();
    $this->travel(2)->hours();
    expect(app(PublishDuePages::class)->handle())->toBe(['published' => 1, 'blocked' => 0]);
    expect($this->page->fresh()->status)->toBe('published')->and($this->page->fresh()->published_at->toIso8601String())->toBe($intended);
    expect(app(PublishDuePages::class)->handle())->toBe(['published' => 0, 'blocked' => 0])->and(Revision::query()->count())->toBe(2);
});

it('rechecks images at scheduled time records one failure and permits repair', function () {
    $image = Media::query()->create(['disk' => 'public', 'path' => 'media/fixture/original.png', 'original_name' => 'fixture.png', 'mime' => 'image/png',
        'size' => 100, 'sha256' => hash('sha256', 'fixture'), 'alt' => 'Synthetic alt', 'processing_status' => 'ready']);
    $this->page->update(['blocks' => [['type' => 'image', 'media_id' => $image->id]]]);
    app(ChangePagePublication::class)->handle($this->page, ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => now()->addHour()->format('Y-m-d\TH:i')]);
    $image->update(['alt' => '']);
    $this->travel(2)->hours();
    expect(app(PublishDuePages::class)->handle())->toBe(['published' => 0, 'blocked' => 1])->and($this->page->fresh()->publication_error)->toBe('validation_failed');
    $version = $this->page->fresh()->content_version;
    expect(app(PublishDuePages::class)->handle())->toBe(['published' => 0, 'blocked' => 1])->and($this->page->fresh()->content_version)->toBe($version)->and(Revision::query()->count())->toBe(2);
    $this->withSession(verifiedAdminSession($this->admin))->get(route('admin.pages.edit', $this->page))->assertSee('Scheduled publication is blocked');
    $image->update(['alt' => 'Repaired alt']);
    expect(app(PublishDuePages::class)->handle())->toBe(['published' => 1, 'blocked' => 0])->and($this->page->fresh()->publication_error)->toBeNull();
    expect(Activity::query()->where('event', 'page_scheduled_published')->sole()->causer_id)->toBeNull();
});

it('rejects stale publication and does not restore publication revisions as content', function () {
    $this->post(route('admin.pages.publication', $this->page), ['operation' => 'publish', 'content_version' => 99])->assertSessionHasErrors('content_version');
    app(ChangePagePublication::class)->handle($this->page, ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => now()->addHour()->format('Y-m-d\TH:i')]);
    $revision = Revision::query()->sole();
    $this->post(route('admin.pages.publication', $this->page), ['operation' => 'cancel', 'content_version' => 2])->assertSessionHasNoErrors();
    $this->post(route('admin.pages.revision', [$this->page, $revision]), ['content_version' => 3])->assertNotFound();
});

it('enforces publisher permissions CSRF and authentication', function () {
    $user = adminAccount('SEO Manager');
    $this->actingAs($user)->withSession(verifiedAdminSession($user));
    $this->post(route('admin.pages.publication', $this->page), ['operation' => 'publish', 'content_version' => 1])->assertForbidden();
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
    $this->app['env'] = 'local';
    $this->post(route('admin.pages.publication', $this->page), ['operation' => 'publish', 'content_version' => 1])->assertStatus(419);
    auth()->logout();
    $this->flushSession();
    $this->get(route('admin.pages.edit', $this->page))->assertRedirect(route('admin.login'));
    expect($this->page->fresh()->status)->toBe('draft');
});

it('bounds cron batches honors content locks and rejects invalid command limits', function () {
    for ($i = 0; $i < 2; $i++) {
        $page = $i === 0 ? $this->page : $this->page->replicate()->fill(['path' => '/another-page']);
        $page->save();
        app(ChangePagePublication::class)->handle($page, ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => now()->addHour()->format('Y-m-d\TH:i')]);
    }
    $this->travel(2)->hours();
    $lock = Cache::lock('content-path-writes', 120);
    $lock->get();
    expect(app(PublishDuePages::class)->handle(1))->toBe(['published' => 0, 'blocked' => 0]);
    $lock->release();
    $this->artisan('trivo:publish-pages', ['--limit' => '1'])->expectsOutput('Published: 1. Blocked: 0.')->assertSuccessful();
    expect(Page::query()->where('status', 'scheduled')->count())->toBe(1);
    $this->artisan('trivo:publish-pages', ['--limit' => '101'])->assertExitCode(2);
});

it('rotates failed batches so later repaired schedules are retried', function () {
    $first = $this->page;
    $second = $first->replicate()->fill(['path' => '/later-repair']);
    $second->save();
    foreach ([$first, $second] as $page) {
        app(ChangePagePublication::class)->handle($page, ['operation' => 'schedule', 'content_version' => 1, 'scheduled_for' => now()->addHour()->format('Y-m-d\TH:i')]);
        $page->update(['seo_title' => null]);
    }
    $this->travel(2)->hours();
    expect(app(PublishDuePages::class)->handle())->toBe(['published' => 0, 'blocked' => 2]);
    $second->update(['seo_title' => 'Repaired title']);
    $this->travel(1)->minutes();
    expect(app(PublishDuePages::class)->handle(1))->toBe(['published' => 0, 'blocked' => 1]);
    $this->travel(1)->minutes();
    expect(app(PublishDuePages::class)->handle(1))->toBe(['published' => 1, 'blocked' => 0]);
});
