<?php

use App\Actions\Admin\SaveBlogTerm;
use App\Actions\Admin\SavePageDraft;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Revision;
use App\Models\Tag;
use App\Services\Content\BlogTerms;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->seed(RolePermissionSeeder::class);
    $this->admin = adminAccount('Editor');
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
});

/** @return array<string, mixed> */
function blogTermFixture(string $kind): array
{
    return ['name' => 'Synthetic '.$kind, 'slug' => 'synthetic-'.$kind, 'path' => '/'.$kind.'/Synthetic-term/', 'description' => 'Synthetic term description.'];
}

function syntheticBlogTerm(string $kind, array $values = []): Category|Tag
{
    return app(SaveBlogTerm::class)->handle(app(BlogTerms::class)->model($kind), array_replace(blogTermFixture($kind), $values));
}

it('creates exact-path terms and excludes source SEO and privileged fields', function (string $kind) {
    $this->post(route('admin.blog.terms.store', $kind), blogTermFixture($kind) + ['legacy_wp_id' => 123, 'seo_title' => 'Injected SEO', 'schema_json' => ['injected' => true], 'content_version' => 99])->assertSessionHasNoErrors()->assertRedirect();
    $term = app(BlogTerms::class)->model($kind)->newQuery()->sole();
    expect($term->path)->toBe('/'.$kind.'/Synthetic-term/')->and($term->legacy_wp_id)->toBeNull()->and($term->getAttribute('seo_title'))->toBeNull()->and($term->content_version)->toBe(1)
        ->and(Revision::query()->sole()->revisionable_type)->toBe($term::class);
    $this->get(route('admin.blog.terms.edit', ['kind' => $kind, 'term' => $term->id]))->assertOk()->assertSee('Save blog term');
    $this->get($term->path)->assertNotFound();
})->with(['categories', 'tags']);

it('rejects unsafe names descriptions slugs and paths without mutations', function (array $values, string $field) {
    foreach (['categories', 'tags'] as $kind) {
        $this->post(route('admin.blog.terms.store', $kind), array_replace(blogTermFixture($kind), $values))->assertSessionHasErrors($field);
    }
    expect(Category::query()->count())->toBe(0)->and(Tag::query()->count())->toBe(0)->and(Revision::query()->count())->toBe(0);
})->with([
    [['name' => '<script>bad()</script>'], 'name'], [['description' => '<b>Bad</b>'], 'description'], [['slug' => '../bad'], 'slug'],
    [['slug' => 'bad%escape'], 'slug'], [['path' => '/'], 'path'], [['path' => '/wp-admin'], 'path'], [['path' => '/bad?query'], 'path'],
]);

it('preserves SEO fields keeps saved paths fixed and rejects stale term saves', function (string $kind) {
    $term = syntheticBlogTerm($kind);
    $term->update(['seo_title' => 'Existing source SEO', 'schema_json' => ['source' => true]]);
    $params = ['kind' => $kind, 'term' => $term->id];
    $this->put(route('admin.blog.terms.update', $params), array_replace(blogTermFixture($kind), ['name' => 'Changed name', 'content_version' => 1]))->assertSessionHasNoErrors();
    $this->put(route('admin.blog.terms.update', $params), blogTermFixture($kind) + ['content_version' => 1])->assertSessionHasErrors('content_version');
    $this->put(route('admin.blog.terms.update', $params), array_replace(blogTermFixture($kind), ['path' => '/renamed', 'content_version' => 2]))->assertSessionHasErrors('path');
    expect($term->fresh()->name)->toBe('Changed name')->and($term->fresh()->getAttribute('seo_title'))->toBe('Existing source SEO')
        ->and($term->fresh()->getAttribute('schema_json'))->toBe(['source' => true])->and($term->fresh()->content_version)->toBe(2);
})->with(['categories', 'tags']);

it('reserves cross-model and archived path aliases while allowing the exact owner', function (string $kind) {
    $page = Page::query()->create(['title' => 'Fixture', 'path' => '/occupied-path']);
    $term = syntheticBlogTerm($kind);
    expect($page->id)->toBe($term->id);
    $this->put(route('admin.blog.terms.update', ['kind' => $kind, 'term' => $term->id]), blogTermFixture($kind) + ['content_version' => 1])->assertSessionHasNoErrors();
    $this->post(route('admin.blog.terms.store', $kind), array_replace(blogTermFixture($kind), ['path' => '/OCCUPIED-path/']))->assertSessionHasErrors('path');
    expect(fn () => app(SavePageDraft::class)->handle(['title' => 'Collision', 'path' => '/'.$kind.'/synthetic-term', 'blocks' => []]))->toThrow(ValidationException::class);
    $this->delete(route('admin.blog.terms.archive', ['kind' => $kind, 'term' => $term->id]), ['content_version' => 2])->assertSessionHasNoErrors();
    $this->post(route('admin.blog.terms.store', $kind), blogTermFixture($kind))->assertSessionHasErrors('path');
})->with(['categories', 'tags']);

it('restores only the matching taxonomy revision preserving the current path and SEO', function () {
    $category = syntheticBlogTerm('categories');
    $revision = Revision::query()->sole();
    $category->update(['seo_title' => 'Existing SEO']);
    app(SaveBlogTerm::class)->handle($category, array_replace(blogTermFixture('categories'), ['name' => 'Changed', 'content_version' => 1]));
    $params = ['kind' => 'categories', 'term' => $category->id, 'revision' => $revision->id];
    $this->post(route('admin.blog.terms.revision', $params), ['content_version' => 2])->assertSessionHasNoErrors();
    expect($category->fresh()->name)->toBe('Synthetic categories')->and($category->fresh()->getAttribute('seo_title'))->toBe('Existing SEO')->and($category->fresh()->content_version)->toBe(3);
    $tag = syntheticBlogTerm('tags');
    expect($tag->id)->toBe($category->id);
    $this->post(route('admin.blog.terms.revision', ['kind' => 'tags', 'term' => $tag->id, 'revision' => $revision->id]), ['content_version' => 1])->assertNotFound();
    $other = syntheticBlogTerm('categories', ['path' => '/other-category']);
    $this->post(route('admin.blog.terms.revision', ['kind' => 'categories', 'term' => $other->id, 'revision' => $revision->id]), ['content_version' => 1])->assertNotFound();
    $wrong = Revision::query()->create(['revisionable_type' => Category::class, 'revisionable_id' => $category->id, 'snapshot' => ['kind' => 'seo', 'content' => []]]);
    $this->post(route('admin.blog.terms.revision', array_replace($params, ['revision' => $wrong->id])), ['content_version' => 3])->assertNotFound();
});

it('validates category ancestors cycles descendants and archive restore ordering', function () {
    $root = syntheticBlogTerm('categories');
    $child = syntheticBlogTerm('categories', ['path' => '/child-category', 'parent_id' => $root->id]);
    $this->put(route('admin.blog.terms.update', ['kind' => 'categories', 'term' => $root->id]), blogTermFixture('categories') + ['parent_id' => $child->id, 'content_version' => 1])->assertSessionHasErrors('parent_id');
    $parent = $child;
    for ($i = 3; $i <= 5; $i++) {
        $parent = syntheticBlogTerm('categories', ['path' => '/level-'.$i, 'parent_id' => $parent->id]);
    }
    $this->post(route('admin.blog.terms.store', 'categories'), array_replace(blogTermFixture('categories'), ['path' => '/level-6', 'parent_id' => $parent->id]))->assertSessionHasErrors('parent_id');
    $other = syntheticBlogTerm('categories', ['path' => '/another-root']);
    $this->put(route('admin.blog.terms.update', ['kind' => 'categories', 'term' => $root->id]), blogTermFixture('categories') + ['parent_id' => $other->id, 'content_version' => 1])->assertSessionHasErrors('parent_id');
    $this->delete(route('admin.blog.terms.archive', ['kind' => 'categories', 'term' => $root->id]), ['content_version' => 1])->assertSessionHasErrors('parent_id');
    $this->delete(route('admin.blog.terms.archive', ['kind' => 'categories', 'term' => $other->id]), ['content_version' => 1])->assertSessionHasNoErrors();
    $this->post(route('admin.blog.terms.store', 'categories'), array_replace(blogTermFixture('categories'), ['path' => '/inactive-parent', 'parent_id' => $other->id]))->assertSessionHasErrors('parent_id');
    $this->post(route('admin.blog.terms.store', 'tags'), blogTermFixture('tags') + ['parent_id' => $root->id])->assertSessionHasErrors('parent_id');
});

it('restores archived category parents before their children', function () {
    $parent = syntheticBlogTerm('categories');
    $child = syntheticBlogTerm('categories', ['path' => '/archived-child', 'parent_id' => $parent->id]);
    $parentParams = ['kind' => 'categories', 'term' => $parent->id];
    $childParams = ['kind' => 'categories', 'term' => $child->id];
    $this->delete(route('admin.blog.terms.archive', $childParams), ['content_version' => 1])->assertSessionHasNoErrors();
    $this->delete(route('admin.blog.terms.archive', $parentParams), ['content_version' => 1])->assertSessionHasNoErrors();
    $this->post(route('admin.blog.terms.restore', $childParams), ['content_version' => 2])->assertSessionHasErrors('parent_id');
    $this->post(route('admin.blog.terms.restore', $parentParams), ['content_version' => 2])->assertSessionHasNoErrors();
    $this->post(route('admin.blog.terms.restore', $childParams), ['content_version' => 2])->assertSessionHasNoErrors();
    expect($child->fresh()->parent_id)->toBe($parent->id)->and($child->fresh()->content_version)->toBe(3);
});

it('archives restores and protects imported or assigned terms', function (string $kind) {
    $term = syntheticBlogTerm($kind);
    $params = ['kind' => $kind, 'term' => $term->id];
    $this->delete(route('admin.blog.terms.archive', $params), ['content_version' => 99])->assertSessionHasErrors('content_version');
    $this->delete(route('admin.blog.terms.archive', $params), ['content_version' => 1])->assertSessionHasNoErrors();
    $this->get(route('admin.blog.terms.edit', $params))->assertOk()->assertSee('Restore blog term');
    $this->post(route('admin.blog.terms.restore', $params), ['content_version' => 1])->assertSessionHasErrors('content_version');
    $this->post(route('admin.blog.terms.restore', $params), ['content_version' => 2])->assertSessionHasNoErrors();
    $term->refresh();
    expect($term->content_version)->toBe(3)->and($term->trashed())->toBeFalse();
    $term->update(['legacy_wp_id' => 123]);
    $this->delete(route('admin.blog.terms.archive', $params), ['content_version' => 3])->assertForbidden();
    $term->update(['legacy_wp_id' => null]);
    $post = Post::query()->create(['title' => 'Synthetic post', 'slug' => 'synthetic', 'path' => '/synthetic-post', 'body' => 'Synthetic body',
        'category_id' => $kind === 'categories' ? $term->id : null]);
    if ($kind === 'tags') {
        DB::table('post_tag')->insert(['post_id' => $post->id, 'tag_id' => $term->id]);
    }
    $this->delete(route('admin.blog.terms.archive', $params), ['content_version' => 3])->assertForbidden();
})->with(['categories', 'tags']);

it('filters terms and keeps SEO managers read only with authentication and CSRF protection', function (string $kind) {
    $term = syntheticBlogTerm($kind);
    syntheticBlogTerm($kind, ['name' => 'Earlier term', 'path' => '/earlier-'.$kind]);
    $this->get(route('admin.blog.terms.index', $kind))->assertSeeInOrder(['Earlier term', 'Synthetic '.$kind]);
    $this->get(route('admin.blog.terms.index', ['kind' => $kind, 'q' => 'Earlier']))->assertSee('Earlier term')->assertDontSee('Synthetic '.$kind);
    $user = adminAccount('SEO Manager');
    $this->actingAs($user)->withSession(verifiedAdminSession($user));
    $this->get(route('admin.blog.terms.edit', ['kind' => $kind, 'term' => $term->id]))->assertOk()->assertDontSee('Save blog term');
    $this->post(route('admin.blog.terms.store', $kind), blogTermFixture($kind))->assertForbidden();
    $this->get(route('admin.blog.terms.index', 'unsupported'))->assertNotFound();
    $this->app['env'] = 'local';
    $this->post(route('admin.blog.terms.store', $kind), blogTermFixture($kind))->assertStatus(419);
    $this->flushSession();
    $this->get(route('admin.blog.terms.index', $kind))->assertRedirect(route('admin.login'));
})->with(['categories', 'tags']);
