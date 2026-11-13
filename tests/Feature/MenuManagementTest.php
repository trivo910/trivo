<?php

use App\Actions\Admin\ChangeMenuItem;
use App\Actions\Admin\SaveMenu;
use App\Models\Menu;
use App\Models\MenuItem;
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
    $this->menu = app(SaveMenu::class)->handle(['name' => 'Synthetic menu', 'location' => 'qa-menu']);
});

/** @return array<string, mixed> */
function menuItemFixture(Menu $menu): array
{
    return ['label' => 'Synthetic link', 'url' => '/synthetic-link/', 'sort' => 5, 'content_version' => $menu->fresh()->content_version];
}

function syntheticMenuItem(Menu $menu, array $values = []): MenuItem
{
    return app(ChangeMenuItem::class)->handle($menu, array_replace(menuItemFixture($menu), $values));
}

it('creates whitelisted menu metadata and stores scoped bounded history', function () {
    $this->post(route('admin.menus.store'), ['name' => 'Another menu', 'location' => 'qa-footer', 'legacy_wp_id' => 123, 'content_version' => 99])->assertSessionHasNoErrors()->assertRedirect();
    $menu = Menu::query()->where('location', 'qa-footer')->sole();
    expect($menu->legacy_wp_id)->toBeNull()->and($menu->content_version)->toBe(1);
    $this->get(route('admin.menus.edit', $menu))->assertOk()->assertSee('Add menu item');
    $item = syntheticMenuItem($menu);
    $snapshot = Revision::query()->where('revisionable_type', Menu::class)->where('revisionable_id', $menu->id)->latest('id')->firstOrFail()->snapshot;
    expect($snapshot['kind'])->toBe('menu')->and($snapshot['item']['id'])->toBe($item->id)->and($snapshot)->not->toHaveKey('items');
});

it('rejects unsafe or duplicate menu metadata', function (array $values, string $field) {
    $this->post(route('admin.menus.store'), array_replace(['name' => 'New menu', 'location' => 'qa-footer'], $values))->assertSessionHasErrors($field);
    expect(Menu::query()->count())->toBe(1)->and(Revision::query()->count())->toBe(1);
})->with([
    [['name' => '<b>Bad</b>'], 'name'], [['location' => 'Header'], 'location'], [['location' => '../bad'], 'location'], [['location' => 'qa-menu'], 'location'],
]);

it('accepts explicit safe menu link formats without altering URLs', function (string $url) {
    $this->post(route('admin.menus.items.store', $this->menu), menuItemFixture($this->menu) + ['legacy_wp_id' => 321, 'menu_id' => 999, 'target' => '_blank'])->assertSessionHasNoErrors();
    $item = MenuItem::query()->sole();
    $this->put(route('admin.menus.items.update', [$this->menu, $item]), array_replace(menuItemFixture($this->menu), ['url' => $url]))->assertSessionHasNoErrors();
    expect($item->fresh()->url)->toBe($url)->and($item->fresh()->legacy_wp_id)->toBeNull()->and($item->fresh()->menu_id)->toBe($this->menu->id);
})->with(['/Exact-path/?page=2#details', 'https://example.test/path#section', '#details']);

it('rejects unsafe links labels targets and ordering atomically', function (array $values, string $field) {
    $this->post(route('admin.menus.items.store', $this->menu), array_replace(menuItemFixture($this->menu), $values))->assertSessionHasErrors($field);
    expect(MenuItem::query()->count())->toBe(0)->and($this->menu->fresh()->content_version)->toBe(1)->and(Revision::query()->count())->toBe(1);
})->with([
    [['url' => 'javascript:alert(1)'], 'url'], [['url' => '//example.test/path'], 'url'], [['url' => 'https://user:pass@example.test/'], 'url'],
    [['url' => 'http://example.test/'], 'url'], [['url' => '/safe/../bad'], 'url'], [['url' => '/%252e%252e/bad'], 'url'],
    [['url' => '/<script>'], 'url'], [['label' => '<b>Bad</b>'], 'label'], [['target' => 'unsafe-window'], 'target'], [['sort' => -1], 'sort'],
]);

it('rejects foreign parents cycles excessive depth and descendant depth changes', function () {
    $otherMenu = app(SaveMenu::class)->handle(['name' => 'Other', 'location' => 'qa-other']);
    $foreign = syntheticMenuItem($otherMenu);
    $this->post(route('admin.menus.items.store', $this->menu), menuItemFixture($this->menu) + ['parent_id' => $foreign->id])->assertSessionHasErrors('parent_id');
    $root = syntheticMenuItem($this->menu);
    $child = syntheticMenuItem($this->menu, ['parent_id' => $root->id]);
    $this->put(route('admin.menus.items.update', [$this->menu, $root]), menuItemFixture($this->menu) + ['parent_id' => $child->id])->assertSessionHasErrors('parent_id');
    $parent = $child;
    for ($depth = 3; $depth <= 5; $depth++) {
        $parent = syntheticMenuItem($this->menu, ['parent_id' => $parent->id]);
    }
    $this->post(route('admin.menus.items.store', $this->menu), menuItemFixture($this->menu) + ['parent_id' => $parent->id])->assertSessionHasErrors('parent_id');
    $newRoot = syntheticMenuItem($this->menu);
    $this->put(route('admin.menus.items.update', [$this->menu, $root]), menuItemFixture($this->menu) + ['parent_id' => $newRoot->id])->assertSessionHasErrors('parent_id');
    expect(MenuItem::query()->where('menu_id', $this->menu->id)->count())->toBe(6);
});

it('bounds active menu item count', function () {
    for ($i = 0; $i < 100; $i++) {
        MenuItem::query()->create(['menu_id' => $this->menu->id, 'label' => 'Synthetic '.$i, 'url' => '/synthetic-'.$i, 'sort' => $i]);
    }
    $this->post(route('admin.menus.items.store', $this->menu), menuItemFixture($this->menu))->assertSessionHasErrors('label');
    expect(MenuItem::query()->count())->toBe(100)->and($this->menu->fresh()->content_version)->toBe(1);
});

it('scopes item reads updates archives and restores to the owning menu', function () {
    $item = syntheticMenuItem($this->menu);
    $otherMenu = app(SaveMenu::class)->handle(['name' => 'Other', 'location' => 'qa-other']);
    $this->get(route('admin.menus.edit', ['menu' => $otherMenu, 'item' => $item->id]))->assertNotFound();
    $this->put(route('admin.menus.items.update', [$otherMenu, $item]), menuItemFixture($otherMenu))->assertNotFound();
    $this->delete(route('admin.menus.items.archive', [$otherMenu, $item]), ['content_version' => 1])->assertNotFound();
    $this->delete(route('admin.menus.items.archive', [$this->menu, $item]), ['content_version' => 2])->assertSessionHasNoErrors();
    $this->post(route('admin.menus.items.restore', [$otherMenu, $item]), ['content_version' => 1])->assertNotFound();
});

it('prevents orphaning children and restores parents before archived children', function () {
    $root = syntheticMenuItem($this->menu);
    $child = syntheticMenuItem($this->menu, ['parent_id' => $root->id]);
    $this->delete(route('admin.menus.items.archive', [$this->menu, $root]), ['content_version' => 3])->assertSessionHasErrors('parent_id');
    $this->delete(route('admin.menus.items.archive', [$this->menu, $child]), ['content_version' => 3])->assertSessionHasNoErrors();
    $this->delete(route('admin.menus.items.archive', [$this->menu, $root]), ['content_version' => 4])->assertSessionHasNoErrors();
    $this->post(route('admin.menus.items.restore', [$this->menu, $child]), ['content_version' => 5])->assertSessionHasErrors('parent_id');
    $this->post(route('admin.menus.items.restore', [$this->menu, $root]), ['content_version' => 5])->assertSessionHasNoErrors();
    $this->post(route('admin.menus.items.restore', [$this->menu, $child]), ['content_version' => 6])->assertSessionHasNoErrors();
    expect($child->fresh()->parent_id)->toBe($root->id)->and($this->menu->fresh()->content_version)->toBe(7);
});

it('rejects stale metadata item and archive changes and retains archived locations', function () {
    $item = syntheticMenuItem($this->menu);
    $this->put(route('admin.menus.update', $this->menu), ['name' => 'Changed', 'location' => 'qa-menu', 'content_version' => 1])->assertSessionHasErrors('content_version');
    $this->put(route('admin.menus.items.update', [$this->menu, $item]), array_replace(menuItemFixture($this->menu), ['content_version' => 1]))->assertSessionHasErrors('content_version');
    $this->delete(route('admin.menus.items.archive', [$this->menu, $item]), ['content_version' => 1])->assertSessionHasErrors('content_version');
    $this->delete(route('admin.menus.archive', $this->menu), ['content_version' => 1])->assertSessionHasErrors('content_version');
    $version = $this->menu->fresh()->content_version;
    $this->delete(route('admin.menus.archive', $this->menu), ['content_version' => $version])->assertSessionHasNoErrors();
    $this->get(route('admin.menus.edit', $this->menu))->assertOk()->assertSee('Restore menu')->assertDontSee('Save menu item');
    $this->post(route('admin.menus.store'), ['name' => 'Collision', 'location' => 'qa-menu'])->assertSessionHasErrors('location');
    $this->post(route('admin.menus.restore', $this->menu), ['content_version' => $version + 1])->assertSessionHasNoErrors();
    expect(MenuItem::query()->count())->toBe(1)->and($this->menu->fresh()->content_version)->toBe($version + 2);
});

it('preserves imported identifiers and protects imported locations and archive boundaries', function () {
    $item = syntheticMenuItem($this->menu);
    $this->menu->update(['legacy_wp_id' => 123]);
    $item->update(['legacy_wp_id' => 456]);
    $this->put(route('admin.menus.update', $this->menu), ['name' => 'Changed', 'location' => 'renamed', 'content_version' => 2])->assertSessionHasErrors('location');
    $this->delete(route('admin.menus.archive', $this->menu), ['content_version' => 2])->assertForbidden();
    $this->delete(route('admin.menus.items.archive', [$this->menu, $item]), ['content_version' => 2])->assertForbidden();
    $this->put(route('admin.menus.items.update', [$this->menu, $item]), array_replace(menuItemFixture($this->menu), ['label' => 'Changed link', 'legacy_wp_id' => 999]))->assertSessionHasNoErrors();
    expect($item->fresh()->legacy_wp_id)->toBe(456)->and($this->menu->fresh()->legacy_wp_id)->toBe(123);
});

it('orders filters and authorizes menus independently and requires second factor and CSRF', function () {
    syntheticMenuItem($this->menu, ['label' => 'Later', 'sort' => 10]);
    syntheticMenuItem($this->menu, ['label' => 'Earlier', 'sort' => 0]);
    $this->get(route('admin.menus.edit', $this->menu))->assertOk()->assertSee('Earlier')->assertSee('Later');
    $this->get(route('admin.menus.index', ['q' => 'missing']))->assertDontSee('Synthetic menu');
    $user = adminAccount('SEO Manager');
    $this->actingAs($user)->withSession(verifiedAdminSession($user));
    $this->get(route('admin.menus.index'))->assertOk()->assertDontSee('Create menu');
    $this->get(route('admin.menus.edit', $this->menu))->assertOk()->assertDontSee('Save menu item');
    $this->post(route('admin.menus.items.store', $this->menu), menuItemFixture($this->menu))->assertForbidden();
    $this->delete(route('admin.menus.archive', $this->menu), ['content_version' => 3])->assertForbidden();
    $this->app['env'] = 'local';
    $this->post(route('admin.menus.store'), ['name' => 'CSRF', 'location' => 'csrf'])->assertStatus(419);
    $this->flushSession();
    $this->get(route('admin.menus.index'))->assertRedirect(route('admin.login'));
});
