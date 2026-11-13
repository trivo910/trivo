<?php

use App\Actions\Admin\ArchiveMedia;
use App\Actions\Admin\SaveFaqDraft;
use App\Actions\Admin\SavePageDraft;
use App\Models\Client;
use App\Models\Faq;
use App\Models\Media;
use App\Models\Page;
use App\Models\Revision;
use App\Models\Testimonial;
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
function faqDraftFixture(): array
{
    return ['title' => 'Synthetic question?', 'sort' => 10,
        'blocks' => [['type' => 'paragraph', 'text' => 'Synthetic answer.']]];
}

it('creates embedded FAQ drafts without inventing URLs or accepting privileged fields', function () {
    $this->post(route('admin.faqs.store'), faqDraftFixture() + ['status' => 'published', 'legacy_wp_id' => 123,
        'body' => '<script>bad()</script>', 'details' => ['injected' => true], 'published_at' => now()])->assertSessionHasNoErrors()->assertRedirect();
    $item = Faq::query()->sole();
    expect($item->path)->toBeNull()->and($item->status)->toBe('draft')->and($item->published_at)->toBeNull()
        ->and($item->legacy_wp_id)->toBeNull()->and($item->getAttribute('body'))->toBeNull()->and($item->getAttribute('details'))->toBeNull()
        ->and($item->content_version)->toBe(1)->and(Revision::query()->sole()->revisionable_type)->toBe(Faq::class);
    app(SaveFaqDraft::class)->handle(faqDraftFixture());
    expect(Faq::query()->whereNull('path')->count())->toBe(2);
    $this->get(route('admin.faqs.edit', $item))->assertOk()->assertSee('FAQ question')->assertSee('Answer sections')->assertDontSee('Publish now');
});

it('validates unsafe answers paths images and display ordering', function (array $values, string $field) {
    $this->post(route('admin.faqs.store'), array_replace(faqDraftFixture(), $values))->assertSessionHasErrors($field);
    expect(Faq::query()->count())->toBe(0)->and(Revision::query()->count())->toBe(0);
})->with([
    [['blocks_json' => '{bad'], 'blocks'], [['blocks' => [['type' => 'paragraph', 'text' => '<script>bad()</script>']]], 'blocks.0.text'],
    [['blocks' => [['type' => 'html', 'text' => 'bad']]], 'blocks.0.type'], [['blocks' => [['type' => 'paragraph', 'text' => 'ok', 'script' => 'bad']]], 'blocks.0'],
    [['path' => '/'], 'path'], [['path' => '/wp-admin'], 'path'], [['path' => '/faq?bad'], 'path'], [['sort' => -1], 'sort'],
    [['media_id' => ['malformed']], 'media_id'], [['title' => '<b>Question?</b>'], 'title'],
]);

it('retains source fields and rejects stale saves and stale archive or restore requests', function () {
    $item = app(SaveFaqDraft::class)->handle(faqDraftFixture());
    $item->update(['body' => 'Source answer', 'details' => ['source' => true]]);
    $this->put(route('admin.faqs.update', $item), array_replace(faqDraftFixture(), ['title' => 'Changed?', 'content_version' => 1]))->assertSessionHasNoErrors();
    $this->put(route('admin.faqs.update', $item), faqDraftFixture() + ['content_version' => 1])->assertSessionHasErrors('content_version');
    $this->delete(route('admin.faqs.archive', $item), ['content_version' => 1])->assertSessionHasErrors('content_version');
    expect($item->fresh()->getAttribute('body'))->toBe('Source answer')->and($item->fresh()->getAttribute('details'))->toBe(['source' => true])
        ->and($item->fresh()->content_version)->toBe(2)->and(Revision::query()->count())->toBe(2);
    $this->delete(route('admin.faqs.archive', $item), ['content_version' => 2])->assertSessionHasNoErrors();
    $this->post(route('admin.faqs.restore', $item), ['content_version' => 2])->assertSessionHasErrors('content_version');
    $this->post(route('admin.faqs.restore', $item), ['content_version' => 3])->assertSessionHasNoErrors();
    expect($item->fresh()->trashed())->toBeFalse()->and($item->fresh()->content_version)->toBe(4);
});

it('preserves optional exact paths and reserves cross-module and archived aliases', function () {
    $item = app(SaveFaqDraft::class)->handle(faqDraftFixture() + ['path' => '/Synthetic-FAQ/']);
    expect($item->path)->toBe('/Synthetic-FAQ/');
    $page = Page::query()->create(['title' => 'Page fixture', 'path' => '/occupied-page']);
    Client::query()->create(['title' => 'Client fixture', 'path' => '/occupied-client']);
    Testimonial::query()->create(['title' => 'Testimonial fixture', 'path' => '/occupied-testimonial']);
    expect($page->id)->toBe($item->id);
    foreach (['/OCCUPIED-page/', '/occupied-client/', '/occupied-testimonial/'] as $path) {
        $this->put(route('admin.faqs.update', $item), faqDraftFixture() + ['path' => $path, 'content_version' => 1])->assertSessionHasErrors('path');
    }
    expect(fn () => app(SavePageDraft::class)->handle(['title' => 'Collision', 'path' => '/synthetic-faq', 'blocks' => []]))->toThrow(ValidationException::class);
    $this->delete(route('admin.faqs.archive', $item), ['content_version' => 1])->assertSessionHasNoErrors();
    $this->post(route('admin.faqs.store'), faqDraftFixture() + ['path' => '/synthetic-faq'])->assertSessionHasErrors('path');
});

it('restores only matching content revisions and retains the current nullable path', function () {
    $item = app(SaveFaqDraft::class)->handle(faqDraftFixture() + ['path' => '/old-faq']);
    $revision = Revision::query()->sole();
    app(SaveFaqDraft::class)->handle(faqDraftFixture() + ['path' => null, 'content_version' => 1], $item);
    $this->post(route('admin.faqs.revision', [$item, $revision]), ['content_version' => 2])->assertSessionHasNoErrors();
    expect($item->fresh()->path)->toBeNull()->and($item->fresh()->content_version)->toBe(3);
    $other = app(SaveFaqDraft::class)->handle(faqDraftFixture());
    $this->post(route('admin.faqs.revision', [$other, $revision]), ['content_version' => 1])->assertNotFound();
    foreach ([['revisionable_type' => Page::class], ['snapshot' => ['kind' => 'seo', 'content_version' => 1]]] as $change) {
        $wrong = Revision::query()->create(array_replace(['revisionable_type' => Faq::class, 'revisionable_id' => $item->id, 'snapshot' => $revision->snapshot], $change));
        $this->post(route('admin.faqs.revision', [$item, $wrong]), ['content_version' => 3])->assertNotFound();
    }
});

it('rechecks images and protects archived and historical image references', function () {
    $image = Media::query()->create(['disk' => 'public', 'path' => 'media/11111111-1111-4111-8111-111111111111/original.png', 'original_name' => 'fixture.png',
        'mime' => 'image/png', 'size' => 100, 'alt' => 'Synthetic FAQ image', 'sha256' => hash('sha256', 'fixture'), 'processing_status' => 'ready']);
    $item = app(SaveFaqDraft::class)->handle(faqDraftFixture() + ['media_id' => $image->id]);
    app(SaveFaqDraft::class)->handle(faqDraftFixture() + ['content_version' => 1], $item);
    expect(fn () => app(ArchiveMedia::class)->handle($image))->toThrow(ValidationException::class);
    $image->update(['alt' => '']);
    $this->post(route('admin.faqs.revision', [$item, Revision::query()->oldest('id')->firstOrFail()]), ['content_version' => 2])->assertSessionHasErrors();
    $this->post(route('admin.faqs.store'), faqDraftFixture() + ['media_id' => $image->id])->assertSessionHasErrors();
    expect(Faq::query()->count())->toBe(1)->and($item->fresh()->content_version)->toBe(2);
});

it('locks imported paths and keeps scheduled published and archived FAQ content read only', function () {
    $item = app(SaveFaqDraft::class)->handle(faqDraftFixture());
    $item->update(['legacy_wp_id' => 123]);
    $this->put(route('admin.faqs.update', $item), faqDraftFixture() + ['path' => '/invented-path', 'content_version' => 1])->assertSessionHasErrors('path');
    $this->delete(route('admin.faqs.archive', $item), ['content_version' => 1])->assertForbidden();
    foreach (['scheduled', 'published'] as $status) {
        $item->update(['status' => $status]);
        $this->put(route('admin.faqs.update', $item), faqDraftFixture() + ['content_version' => 1])->assertForbidden();
        $this->get(route('admin.faqs.edit', $item))->assertOk()->assertDontSee('Save faq draft');
    }
    $item->update(['status' => 'draft', 'legacy_wp_id' => null]);
    $this->delete(route('admin.faqs.archive', $item), ['content_version' => 1])->assertSessionHasNoErrors();
    $this->put(route('admin.faqs.update', $item), faqDraftFixture() + ['content_version' => 2])->assertNotFound();
    $this->get(route('admin.faqs.edit', $item))->assertOk()->assertSee('Restore faq draft');
});

it('orders and filters drafts and applies FAQ permissions independently', function () {
    $item = app(SaveFaqDraft::class)->handle(faqDraftFixture());
    app(SaveFaqDraft::class)->handle(array_replace(faqDraftFixture(), ['title' => 'Earlier question?', 'sort' => 1]));
    $this->get(route('admin.faqs.index'))->assertSeeInOrder(['Earlier question?', $item->title]);
    $this->get(route('admin.faqs.index', ['q' => 'Earlier']))->assertSee('Earlier question?')->assertDontSee('Synthetic question?');
    $this->get(route('admin.faqs.index', ['state' => 'invalid']))->assertSessionHasErrors('state');
    $user = adminAccount('SEO Manager');
    $this->actingAs($user)->withSession(verifiedAdminSession($user));
    $this->get(route('admin.faqs.index'))->assertOk()->assertDontSee('Create faq draft');
    $this->post(route('admin.faqs.store'), faqDraftFixture())->assertForbidden();
    $this->put(route('admin.faqs.update', $item), faqDraftFixture() + ['content_version' => 1])->assertForbidden();
    $this->delete(route('admin.faqs.archive', $item), ['content_version' => 1])->assertForbidden();
    $user->roles()->firstOrFail()->revokePermissionTo('faqs.view');
    $this->actingAs($user->refresh())->withSession(verifiedAdminSession($user));
    $this->get(route('admin.faqs.index'))->assertForbidden();
});

it('requires authentication second factor and CSRF for FAQ content', function () {
    $this->app['env'] = 'local';
    $this->post(route('admin.faqs.store'), faqDraftFixture())->assertStatus(419);
    auth()->logout();
    $this->flushSession();
    $this->get(route('admin.faqs.index'))->assertRedirect(route('admin.login'));
    $this->actingAs($this->admin);
    $this->get(route('admin.faqs.index'))->assertRedirect(route('admin.login'));
    expect(Faq::query()->count())->toBe(0);
});
