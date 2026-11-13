<?php

use App\Actions\Admin\ArchiveMedia;
use App\Actions\Admin\SaveFaqDraft;
use App\Actions\Admin\SaveTestimonialDraft;
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
function testimonialDraftFixture(): array
{
    return ['title' => 'Synthetic testimonial', 'sort' => 10,
        'blocks' => [['type' => 'paragraph', 'text' => 'Synthetic quote for testing.']]];
}

it('creates optional-path testimonial drafts and whitelists fields', function () {
    $this->post(route('admin.testimonials.store'), testimonialDraftFixture() + ['status' => 'published', 'legacy_wp_id' => 123,
        'body' => 'Injected body', 'details' => ['injected' => true], 'published_at' => now(), 'content_version' => 99])->assertSessionHasNoErrors()->assertRedirect();
    $item = Testimonial::query()->sole();
    expect($item->path)->toBeNull()->and($item->status)->toBe('draft')->and($item->legacy_wp_id)->toBeNull()->and($item->published_at)->toBeNull()
        ->and($item->getAttribute('body'))->toBeNull()->and($item->getAttribute('details'))->toBeNull()->and($item->content_version)->toBe(1);
    app(SaveTestimonialDraft::class)->handle(testimonialDraftFixture());
    expect(Testimonial::query()->whereNull('path')->count())->toBe(2)->and(Revision::query()->count())->toBe(2);
    $this->get(route('admin.testimonials.edit', $item))->assertOk()->assertSee('Quote sections')->assertDontSee('Publish now');
});

it('rejects unsafe testimonial input without saving revisions', function (array $values, string $field) {
    $this->post(route('admin.testimonials.store'), array_replace(testimonialDraftFixture(), $values))->assertSessionHasErrors($field);
    expect(Testimonial::query()->count())->toBe(0)->and(Revision::query()->count())->toBe(0);
})->with([
    [['blocks_json' => '{bad'], 'blocks'], [['blocks' => [['type' => 'paragraph', 'text' => '<script>bad()</script>']]], 'blocks.0.text'],
    [['blocks' => [['type' => 'link', 'label' => 'Unsafe', 'url' => 'javascript:alert(1)']]], 'blocks.0.url'],
    [['title' => '<b>Unsafe</b>'], 'title'], [['path' => '/'], 'path'], [['path' => '/wp-admin'], 'path'], [['sort' => 10001], 'sort'],
]);

it('preserves source fields and keeps testimonial history scoped by model owner and kind', function () {
    $item = app(SaveTestimonialDraft::class)->handle(testimonialDraftFixture() + ['path' => '/Original-quote/']);
    $revision = Revision::query()->sole();
    $item->update(['body' => 'Source quote', 'details' => ['source_author' => 'Synthetic attribution']]);
    $this->put(route('admin.testimonials.update', $item), array_replace(testimonialDraftFixture(), ['title' => 'Changed quote', 'path' => null, 'content_version' => 1]))->assertSessionHasNoErrors();
    $this->put(route('admin.testimonials.update', $item), testimonialDraftFixture() + ['content_version' => 1])->assertSessionHasErrors('content_version');
    $this->post(route('admin.testimonials.revision', [$item, $revision]), ['content_version' => 2])->assertSessionHasNoErrors();
    expect($item->fresh()->title)->toBe('Synthetic testimonial')->and($item->fresh()->path)->toBeNull()->and($item->fresh()->content_version)->toBe(3)
        ->and($item->fresh()->getAttribute('body'))->toBe('Source quote')->and($item->fresh()->getAttribute('details'))->toBe(['source_author' => 'Synthetic attribution']);
    $faq = app(SaveFaqDraft::class)->handle(['title' => 'Synthetic question?', 'blocks' => [], 'sort' => 0]);
    expect($faq->id)->toBe($item->id);
    $this->post(route('admin.testimonials.revision', [$item, Revision::query()->where('revisionable_type', Faq::class)->sole()]), ['content_version' => 3])->assertNotFound();
    $this->post(route('admin.faqs.revision', [$faq, $revision]), ['content_version' => 1])->assertNotFound();
    $other = app(SaveTestimonialDraft::class)->handle(testimonialDraftFixture());
    $this->post(route('admin.testimonials.revision', [$other, $revision]), ['content_version' => 1])->assertNotFound();
    $wrong = Revision::query()->create(['revisionable_type' => Testimonial::class, 'revisionable_id' => $item->id, 'snapshot' => ['kind' => 'seo']]);
    $this->post(route('admin.testimonials.revision', [$item, $wrong]), ['content_version' => 3])->assertNotFound();
});

it('reserves testimonial aliases across modules and archive cycles', function () {
    $item = app(SaveTestimonialDraft::class)->handle(testimonialDraftFixture() + ['path' => '/Synthetic-quote/']);
    $page = Page::query()->create(['title' => 'Page fixture', 'path' => '/occupied-page']);
    expect($page->id)->toBe($item->id);
    $this->put(route('admin.testimonials.update', $item), testimonialDraftFixture() + ['path' => '/OCCUPIED-page/', 'content_version' => 1])->assertSessionHasErrors('path');
    expect(fn () => app(SaveFaqDraft::class)->handle(['title' => 'Collision', 'path' => '/synthetic-quote', 'sort' => 0, 'blocks' => []]))->toThrow(ValidationException::class);
    $this->delete(route('admin.testimonials.archive', $item), ['content_version' => 99])->assertSessionHasErrors('content_version');
    $this->delete(route('admin.testimonials.archive', $item), ['content_version' => 1])->assertSessionHasNoErrors();
    $this->post(route('admin.testimonials.store'), testimonialDraftFixture() + ['path' => '/synthetic-quote'])->assertSessionHasErrors('path');
    $this->put(route('admin.testimonials.update', $item), testimonialDraftFixture() + ['content_version' => 2])->assertNotFound();
    $this->post(route('admin.testimonials.restore', $item), ['content_version' => 1])->assertSessionHasErrors('content_version');
    $this->post(route('admin.testimonials.restore', $item), ['content_version' => 2])->assertSessionHasNoErrors();
    expect($item->fresh()->trashed())->toBeFalse()->and($item->fresh()->content_version)->toBe(3);
});

it('protects historical testimonial images and rechecks readiness on restoration', function () {
    $image = Media::query()->create(['disk' => 'public', 'path' => 'media/11111111-1111-4111-8111-111111111111/original.png', 'original_name' => 'fixture.png',
        'mime' => 'image/png', 'size' => 100, 'alt' => 'Synthetic quote image', 'sha256' => hash('sha256', 'fixture'), 'processing_status' => 'ready']);
    $item = app(SaveTestimonialDraft::class)->handle(testimonialDraftFixture() + ['media_id' => $image->id]);
    $revision = Revision::query()->sole();
    app(SaveTestimonialDraft::class)->handle(testimonialDraftFixture() + ['content_version' => 1], $item);
    expect(fn () => app(ArchiveMedia::class)->handle($image))->toThrow(ValidationException::class);
    $image->update(['processing_status' => 'failed']);
    $this->post(route('admin.testimonials.revision', [$item, $revision]), ['content_version' => 2])->assertSessionHasErrors();
    $this->post(route('admin.testimonials.store'), testimonialDraftFixture() + ['media_id' => $image->id])->assertSessionHasErrors();
    expect($item->fresh()->content_version)->toBe(2)->and(Revision::query()->count())->toBe(2);
});

it('protects imported and published testimonials and applies testimonial permissions independently', function () {
    $item = app(SaveTestimonialDraft::class)->handle(testimonialDraftFixture());
    $item->update(['legacy_wp_id' => 123]);
    $this->put(route('admin.testimonials.update', $item), testimonialDraftFixture() + ['path' => '/invented', 'content_version' => 1])->assertSessionHasErrors('path');
    $this->delete(route('admin.testimonials.archive', $item), ['content_version' => 1])->assertForbidden();
    foreach (['scheduled', 'published'] as $status) {
        $item->update(['status' => $status]);
        $this->put(route('admin.testimonials.update', $item), testimonialDraftFixture() + ['content_version' => 1])->assertForbidden();
        $this->get(route('admin.testimonials.edit', $item))->assertOk()->assertDontSee('Save testimonial draft');
    }
    $user = adminAccount('SEO Manager');
    $this->actingAs($user)->withSession(verifiedAdminSession($user));
    $this->get(route('admin.testimonials.index'))->assertOk()->assertDontSee('Create testimonial draft');
    $this->post(route('admin.testimonials.store'), testimonialDraftFixture())->assertForbidden();
    $user->roles()->firstOrFail()->revokePermissionTo('testimonials.view');
    $this->actingAs($user->refresh())->withSession(verifiedAdminSession($user));
    $this->get(route('admin.testimonials.index'))->assertForbidden();
});

it('orders and filters testimonials and requires authenticated second factor sessions and CSRF', function () {
    app(SaveTestimonialDraft::class)->handle(testimonialDraftFixture());
    app(SaveTestimonialDraft::class)->handle(array_replace(testimonialDraftFixture(), ['title' => 'Earlier quote', 'sort' => 1]));
    $this->get(route('admin.testimonials.index'))->assertSeeInOrder(['Earlier quote', 'Synthetic testimonial']);
    $this->get(route('admin.testimonials.index', ['q' => 'Earlier']))->assertSee('Earlier quote')->assertDontSee('Synthetic testimonial');
    $this->app['env'] = 'local';
    $this->post(route('admin.testimonials.store'), testimonialDraftFixture())->assertStatus(419);
    $this->flushSession();
    $this->get(route('admin.testimonials.index'))->assertRedirect(route('admin.login'));
});
