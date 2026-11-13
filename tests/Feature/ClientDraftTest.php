<?php

use App\Actions\Admin\ArchiveMedia;
use App\Actions\Admin\SaveClientDraft;
use App\Actions\Admin\SaveTestimonialDraft;
use App\Models\Client;
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
function clientDraftFixture(): array
{
    return ['title' => 'Synthetic client', 'sort' => 10,
        'blocks' => [['type' => 'paragraph', 'text' => 'Synthetic client details for testing.']]];
}

it('creates optional-path client drafts and whitelists fields', function () {
    $this->post(route('admin.clients.store'), clientDraftFixture() + ['status' => 'published', 'legacy_wp_id' => 123,
        'body' => 'Injected body', 'details' => ['injected' => true], 'published_at' => now(), 'content_version' => 99])->assertSessionHasNoErrors()->assertRedirect();
    $item = Client::query()->sole();
    expect($item->path)->toBeNull()->and($item->status)->toBe('draft')->and($item->legacy_wp_id)->toBeNull()->and($item->published_at)->toBeNull()
        ->and($item->getAttribute('body'))->toBeNull()->and($item->getAttribute('details'))->toBeNull()->and($item->content_version)->toBe(1);
    $minimal = app(SaveClientDraft::class)->handle(array_replace(clientDraftFixture(), ['blocks' => []]));
    expect($minimal->blocks)->toBe([]);
    expect(Client::query()->whereNull('path')->count())->toBe(2)->and(Revision::query()->count())->toBe(2);
    $this->get(route('admin.clients.edit', $item))->assertOk()->assertSee('Client sections')->assertDontSee('Publish now');
});

it('rejects unsafe client input without saving revisions', function (array $values, string $field) {
    $this->post(route('admin.clients.store'), array_replace(clientDraftFixture(), $values))->assertSessionHasErrors($field);
    expect(Client::query()->count())->toBe(0)->and(Revision::query()->count())->toBe(0);
})->with([
    [['blocks_json' => '{bad'], 'blocks'], [['blocks' => [['type' => 'paragraph', 'text' => '<script>bad()</script>']]], 'blocks.0.text'],
    [['blocks' => [['type' => 'link', 'label' => 'Unsafe', 'url' => 'javascript:alert(1)']]], 'blocks.0.url'],
    [['title' => '<b>Unsafe</b>'], 'title'], [['path' => '/'], 'path'], [['path' => '/wp-admin'], 'path'], [['sort' => 10001], 'sort'],
    [['media_id' => ['malformed']], 'media_id'],
]);

it('preserves source fields and keeps client history scoped by model owner and kind', function () {
    $item = app(SaveClientDraft::class)->handle(clientDraftFixture() + ['path' => '/Original-quote/']);
    $revision = Revision::query()->sole();
    $item->update(['body' => 'Source client details', 'details' => ['source_company' => 'Synthetic company']]);
    $this->put(route('admin.clients.update', $item), array_replace(clientDraftFixture(), ['title' => 'Changed client', 'path' => null, 'content_version' => 1]))->assertSessionHasNoErrors();
    $this->put(route('admin.clients.update', $item), clientDraftFixture() + ['content_version' => 1])->assertSessionHasErrors('content_version');
    $this->post(route('admin.clients.revision', [$item, $revision]), ['content_version' => 2])->assertSessionHasNoErrors();
    expect($item->fresh()->title)->toBe('Synthetic client')->and($item->fresh()->path)->toBeNull()->and($item->fresh()->content_version)->toBe(3)
        ->and($item->fresh()->getAttribute('body'))->toBe('Source client details')->and($item->fresh()->getAttribute('details'))->toBe(['source_company' => 'Synthetic company']);
    $testimonial = app(SaveTestimonialDraft::class)->handle(['title' => 'Synthetic quote', 'blocks' => [], 'sort' => 0]);
    expect($testimonial->id)->toBe($item->id);
    $this->post(route('admin.clients.revision', [$item, Revision::query()->where('revisionable_type', Testimonial::class)->sole()]), ['content_version' => 3])->assertNotFound();
    $this->post(route('admin.testimonials.revision', [$testimonial, $revision]), ['content_version' => 1])->assertNotFound();
    $other = app(SaveClientDraft::class)->handle(clientDraftFixture());
    $this->post(route('admin.clients.revision', [$other, $revision]), ['content_version' => 1])->assertNotFound();
    $wrong = Revision::query()->create(['revisionable_type' => Client::class, 'revisionable_id' => $item->id, 'snapshot' => ['kind' => 'seo']]);
    $this->post(route('admin.clients.revision', [$item, $wrong]), ['content_version' => 3])->assertNotFound();
});

it('reserves client aliases across modules and archive cycles', function () {
    $item = app(SaveClientDraft::class)->handle(clientDraftFixture() + ['path' => '/Synthetic-quote/']);
    $page = Page::query()->create(['title' => 'Page fixture', 'path' => '/occupied-page']);
    expect($page->id)->toBe($item->id);
    $this->put(route('admin.clients.update', $item), clientDraftFixture() + ['path' => '/OCCUPIED-page/', 'content_version' => 1])->assertSessionHasErrors('path');
    expect(fn () => app(SaveTestimonialDraft::class)->handle(['title' => 'Collision', 'path' => '/synthetic-quote', 'sort' => 0, 'blocks' => []]))->toThrow(ValidationException::class);
    $this->delete(route('admin.clients.archive', $item), ['content_version' => 99])->assertSessionHasErrors('content_version');
    $this->delete(route('admin.clients.archive', $item), ['content_version' => 1])->assertSessionHasNoErrors();
    $this->post(route('admin.clients.store'), clientDraftFixture() + ['path' => '/synthetic-quote'])->assertSessionHasErrors('path');
    $this->put(route('admin.clients.update', $item), clientDraftFixture() + ['content_version' => 2])->assertNotFound();
    $this->post(route('admin.clients.restore', $item), ['content_version' => 1])->assertSessionHasErrors('content_version');
    $this->post(route('admin.clients.restore', $item), ['content_version' => 2])->assertSessionHasNoErrors();
    expect($item->fresh()->trashed())->toBeFalse()->and($item->fresh()->content_version)->toBe(3);
});

it('protects historical client images and rechecks readiness on restoration', function () {
    $image = Media::query()->create(['disk' => 'public', 'path' => 'media/11111111-1111-4111-8111-111111111111/original.png', 'original_name' => 'fixture.png',
        'mime' => 'image/png', 'size' => 100, 'alt' => 'Synthetic quote image', 'sha256' => hash('sha256', 'fixture'), 'processing_status' => 'ready']);
    $item = app(SaveClientDraft::class)->handle(clientDraftFixture() + ['media_id' => $image->id]);
    $revision = Revision::query()->sole();
    app(SaveClientDraft::class)->handle(clientDraftFixture() + ['content_version' => 1], $item);
    expect(fn () => app(ArchiveMedia::class)->handle($image))->toThrow(ValidationException::class);
    $image->update(['processing_status' => 'failed']);
    $this->post(route('admin.clients.revision', [$item, $revision]), ['content_version' => 2])->assertSessionHasErrors();
    $this->post(route('admin.clients.store'), clientDraftFixture() + ['media_id' => $image->id])->assertSessionHasErrors();
    expect($item->fresh()->content_version)->toBe(2)->and(Revision::query()->count())->toBe(2);
});

it('protects imported and published clients and applies client permissions independently', function () {
    $item = app(SaveClientDraft::class)->handle(clientDraftFixture());
    $item->update(['legacy_wp_id' => 123]);
    $this->put(route('admin.clients.update', $item), clientDraftFixture() + ['path' => '/invented', 'content_version' => 1])->assertSessionHasErrors('path');
    $this->delete(route('admin.clients.archive', $item), ['content_version' => 1])->assertForbidden();
    foreach (['scheduled', 'published'] as $status) {
        $item->update(['status' => $status]);
        $this->put(route('admin.clients.update', $item), clientDraftFixture() + ['content_version' => 1])->assertForbidden();
        $this->get(route('admin.clients.edit', $item))->assertOk()->assertDontSee('Save client draft');
    }
    $user = adminAccount('SEO Manager');
    $this->actingAs($user)->withSession(verifiedAdminSession($user));
    $this->get(route('admin.clients.index'))->assertOk()->assertDontSee('Create client draft');
    $this->post(route('admin.clients.store'), clientDraftFixture())->assertForbidden();
    $user->roles()->firstOrFail()->revokePermissionTo('clients.view');
    $this->actingAs($user->refresh())->withSession(verifiedAdminSession($user));
    $this->get(route('admin.clients.index'))->assertForbidden();
});

it('orders and filters clients and requires authenticated second factor sessions and CSRF', function () {
    app(SaveClientDraft::class)->handle(clientDraftFixture());
    app(SaveClientDraft::class)->handle(array_replace(clientDraftFixture(), ['title' => 'Earlier client', 'sort' => 1]));
    $this->get(route('admin.clients.index'))->assertSeeInOrder(['Earlier client', 'Synthetic client']);
    $this->get(route('admin.clients.index', ['q' => 'Earlier']))->assertSee('Earlier client')->assertDontSee('Synthetic client');
    $this->app['env'] = 'local';
    $this->post(route('admin.clients.store'), clientDraftFixture())->assertStatus(419);
    $this->flushSession();
    $this->get(route('admin.clients.index'))->assertRedirect(route('admin.login'));
});
