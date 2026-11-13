<?php

use App\Actions\Admin\SaveAuthorProfile;
use App\Models\Author;
use App\Models\Media;
use App\Models\Post;
use App\Models\Revision;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->seed(RolePermissionSeeder::class);
    $this->admin = adminAccount('Editor');
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
});

/** @return array<string, mixed> */
function authorProfileFixture(array $values = []): array
{
    return array_replace(['name' => 'Synthetic author', 'path' => '/author/synthetic-author/', 'bio' => 'Synthetic author biography.',
        'socials' => ['website' => 'https://example.test/team/synthetic']], $values);
}

function syntheticAuthorProfile(array $values = []): Author
{
    return app(SaveAuthorProfile::class)->handle(new Author, authorProfileFixture($values));
}

it('creates an author profile without changing privileged or SEO fields', function () {
    $this->post(route('admin.authors.store'), authorProfileFixture(['legacy_wp_id' => 123, 'seo_title' => 'Injected SEO', 'content_version' => 9]))
        ->assertSessionHasNoErrors()->assertRedirect();
    $author = Author::query()->sole();
    expect($author->legacy_wp_id)->toBeNull()->and($author->getAttribute('seo_title'))->toBeNull()->and($author->content_version)->toBe(1)
        ->and($author->socials)->toBe(['website' => 'https://example.test/team/synthetic'])->and(Revision::query()->sole()->snapshot['kind'])->toBe('author_profile');
    $this->get(route('admin.authors.edit', $author))->assertOk()->assertSee('Save author profile');
    $this->get($author->path)->assertNotFound();
});

it('rejects unsafe author profile data without a mutation', function (array $values, string $field) {
    $this->post(route('admin.authors.store'), authorProfileFixture($values))->assertSessionHasErrors($field);
    expect(Author::query()->count())->toBe(0)->and(Revision::query()->count())->toBe(0);
})->with([
    [['name' => '<script>bad</script>'], 'name'], [['bio' => '<b>Bad</b>'], 'bio'], [['path' => '/wp-admin'], 'path'],
    [['path' => '/author/bad?query'], 'path'], [['socials' => ['website' => 'http://example.test']], 'socials.website'],
    [['socials' => ['website' => 'https://user:pass@example.test']], 'socials.website'],
]);

it('requires a ready accessible avatar and accepts one when ready', function () {
    $media = Media::query()->create(['disk' => 'private', 'path' => 'media/a.png', 'original_name' => 'a.png', 'mime' => 'image/png', 'size' => 1,
        'sha256' => str_repeat('a', 64), 'processing_status' => 'queued']);
    $this->post(route('admin.authors.store'), authorProfileFixture(['avatar_id' => $media->id]))->assertSessionHasErrors('avatar_id');
    $media->update(['processing_status' => 'ready', 'alt' => 'Synthetic avatar']);
    $this->post(route('admin.authors.store'), authorProfileFixture(['avatar_id' => $media->id]))->assertSessionHasNoErrors();
    expect(Author::query()->sole()->avatar_id)->toBe($media->id);
});

it('keeps existing paths and SEO fixed, rejects stale changes and restores only matching revisions', function () {
    $author = syntheticAuthorProfile();
    $revision = Revision::query()->sole();
    $author->update(['seo_title' => 'Existing SEO']);
    $this->put(route('admin.authors.update', $author), authorProfileFixture(['name' => 'Changed author', 'content_version' => 1]))->assertSessionHasNoErrors();
    $this->put(route('admin.authors.update', $author), authorProfileFixture(['content_version' => 1]))->assertSessionHasErrors('content_version');
    $this->put(route('admin.authors.update', $author), authorProfileFixture(['path' => '/author/renamed', 'content_version' => 2]))->assertSessionHasErrors('path');
    $this->post(route('admin.authors.revision', ['author' => $author, 'revision' => $revision]), ['content_version' => 2])->assertSessionHasNoErrors();
    expect($author->fresh()->name)->toBe('Synthetic author')->and($author->fresh()->getAttribute('seo_title'))->toBe('Existing SEO')->and($author->fresh()->content_version)->toBe(3);
    $wrong = Revision::query()->create(['revisionable_type' => Author::class, 'revisionable_id' => $author->id, 'snapshot' => ['kind' => 'seo', 'content' => []]]);
    $this->post(route('admin.authors.revision', ['author' => $author, 'revision' => $wrong]), ['content_version' => 3])->assertNotFound();
});

it('reserves author aliases and protects imported or assigned profiles from archive', function () {
    $author = syntheticAuthorProfile();
    $this->post(route('admin.authors.store'), authorProfileFixture(['path' => '/AUTHOR/SYNTHETIC-AUTHOR']))->assertSessionHasErrors('path');
    $author->update(['legacy_wp_id' => 44]);
    $this->delete(route('admin.authors.archive', $author), ['content_version' => 1])->assertForbidden();
    $author->update(['legacy_wp_id' => null]);
    Post::query()->create(['title' => 'Synthetic post', 'slug' => 'synthetic-post', 'path' => '/synthetic-post', 'body' => 'Synthetic body', 'author_id' => $author->id]);
    $this->delete(route('admin.authors.archive', $author), ['content_version' => 1])->assertForbidden();
});

it('archives restores and maintains permissions CSRF and authentication boundaries', function () {
    $author = syntheticAuthorProfile();
    $this->delete(route('admin.authors.archive', $author), ['content_version' => 99])->assertSessionHasErrors('content_version');
    $this->delete(route('admin.authors.archive', $author), ['content_version' => 1])->assertSessionHasNoErrors();
    $this->get(route('admin.authors.edit', $author))->assertOk()->assertSee('Restore author profile');
    $this->post(route('admin.authors.restore', $author), ['content_version' => 2])->assertSessionHasNoErrors();
    expect($author->fresh()->content_version)->toBe(3)->and($author->fresh()->trashed())->toBeFalse();
    $reader = adminAccount('SEO Manager');
    $this->actingAs($reader)->withSession(verifiedAdminSession($reader));
    $this->get(route('admin.authors.index'))->assertOk();
    $this->post(route('admin.authors.store'), authorProfileFixture())->assertForbidden();
    $this->app['env'] = 'local';
    $this->post(route('admin.authors.store'), authorProfileFixture())->assertStatus(419);
    $this->flushSession();
    $this->get(route('admin.authors.index'))->assertRedirect(route('admin.login'));
});
