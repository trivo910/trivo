<?php

use App\Models\NotFoundLog;
use App\Models\Redirect;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->seed(RolePermissionSeeder::class);
    $this->admin = adminAccount();
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
});

it('creates updates and deletes a literal redirect with policies', function () {
    $this->post(route('admin.redirects.store'), ['from_path' => '/old/', 'to_url' => 'https://trivo.in/current', 'status_code' => 301])->assertRedirect(route('admin.redirects.index'));
    $redirect = Redirect::query()->sole();
    expect($redirect->from_path)->toBe('/old/')->and($redirect->to_url)->toBe('/current');
    $this->get(route('admin.redirects.edit', $redirect))->assertOk()->assertSee('/old/');
    $this->put(route('admin.redirects.update', $redirect), ['from_path' => '/old/', 'to_url' => '/next', 'status_code' => 302])->assertRedirect();
    expect($redirect->fresh()->to_url)->toBe('/next')->and((int) $redirect->fresh()->status_code)->toBe(302);
    $this->delete(route('admin.redirects.destroy', $redirect))->assertRedirect();
    expect(Redirect::query()->count())->toBe(0);
});

it('flattens chains and rejects loops without partial updates', function () {
    $this->post(route('admin.redirects.store'), ['from_path' => '/a', 'to_url' => '/b', 'status_code' => 301]);
    $this->post(route('admin.redirects.store'), ['from_path' => '/b', 'to_url' => '/c', 'status_code' => 301]);
    expect(Redirect::query()->where('from_path', '/a')->value('to_url'))->toBe('/c');
    $this->post(route('admin.redirects.store'), ['from_path' => '/c', 'to_url' => '/a', 'status_code' => 301])->assertSessionHasErrors('to_url');
    expect(Redirect::query()->count())->toBe(2);
});

it('stores 410 without a destination and rejects redirects into a removed path', function () {
    $this->post(route('admin.redirects.store'), ['from_path' => '/removed', 'status_code' => 410])->assertRedirect();
    expect(Redirect::query()->sole()->to_url)->toBeNull();
    $this->post(route('admin.redirects.store'), ['from_path' => '/old', 'to_url' => '/removed', 'status_code' => 301])->assertSessionHasErrors('to_url');
});

it('rejects unsafe destinations', function (string $target) {
    $this->post(route('admin.redirects.store'), ['from_path' => '/old', 'to_url' => $target, 'status_code' => 301])->assertSessionHasErrors('to_url');
    expect(Redirect::query()->count())->toBe(0);
})->with(['https://evil.test/path', '//evil.test', '/%2f%2fevil.test', '/\\evil.test', '/next%0d%0aX-Test:1', '/wp-login.php', '/up', '/a/../b']);

it('rejects protected and query-based source paths', function () {
    foreach (['/', '/up', '/wp-admin', '/robots.txt', '/sitemap_index.xml', '/old?p=1', '/'.config('trivo.admin_path').'/login'] as $source) {
        $this->post(route('admin.redirects.store'), ['from_path' => $source, 'to_url' => '/current', 'status_code' => 301])->assertSessionHasErrors('from_path');
    }
    expect(Redirect::query()->count())->toBe(0);
});

it('imports CSV atomically and updates existing source paths', function () {
    $file = UploadedFile::fake()->createWithContent('redirects.csv', "from_path,to_url,status_code\n/old,/current,301\n/gone,,410\n");
    $this->post(route('admin.redirects.import'), ['csv' => $file])->assertRedirect(route('admin.redirects.index'));
    expect(Redirect::query()->count())->toBe(2);
    $file = UploadedFile::fake()->createWithContent('redirects.csv', "from_path,to_url,status_code\n/old,/new,302\n");
    $this->post(route('admin.redirects.import'), ['csv' => $file])->assertRedirect();
    expect(Redirect::query()->count())->toBe(2)->and(Redirect::query()->where('from_path', '/old')->value('to_url'))->toBe('/new');
    $invalid = UploadedFile::fake()->createWithContent('redirects.csv', "from_path,to_url,status_code\n/valid,/current,301\n/bad,https://evil.test,301\n");
    $this->post(route('admin.redirects.import'), ['csv' => $invalid])->assertSessionHasErrors('csv');
    expect(Redirect::query()->count())->toBe(2);
});

it('rolls back an entire CSV containing a late redirect cycle', function () {
    $file = UploadedFile::fake()->createWithContent('redirects.csv', "from_path,to_url,status_code\n/a,/b,301\n/b,/a,301\n");
    $this->post(route('admin.redirects.import'), ['csv' => $file])->assertSessionHasErrors();
    expect(Redirect::query()->count())->toBe(0);
});

it('records public 404 counts without query strings referrers or raw user agents', function () {
    $this->withHeader('User-Agent', 'Private browser')->withHeader('Referer', 'https://example.test/?email=person@example.test');
    $this->get('/missing-test?email=person@example.test')->assertNotFound();
    $this->get('/missing-test?token=private')->assertNotFound();
    $log = NotFoundLog::query()->sole();
    expect($log->path)->toBe('/missing-test')->and((int) $log->count)->toBe(2)
        ->and($log->referrer)->toBeNull()->and($log->ua_hash)->not->toBe('Private browser');
    $this->get('/'.config('trivo.admin_path').'/unknown')->assertNotFound();
    expect(NotFoundLog::query()->count())->toBe(1);
});

it('prefills a redirect from a permitted 404 log without mutating it', function () {
    $log = NotFoundLog::query()->create(['path' => '/lost-page/', 'last_seen' => now()]);
    $this->get(route('admin.not-found'))->assertOk()->assertSee('/lost-page/');
    $this->get(route('admin.redirects.create', ['not_found' => $log->id]))->assertOk()->assertSee('value="/lost-page/"', false);
    expect(Redirect::query()->count())->toBe(0);
});

it('denies redirect and 404 management to editors while allowing SEO managers', function () {
    $editor = adminAccount('Editor');
    $this->actingAs($editor)->withSession(verifiedAdminSession($editor));
    $this->get(route('admin.redirects.index'))->assertForbidden();
    $this->get(route('admin.not-found'))->assertForbidden();
    $this->post(route('admin.redirects.store'), ['from_path' => '/old', 'to_url' => '/new', 'status_code' => 301])->assertForbidden();
    $seo = adminAccount('SEO Manager');
    $this->actingAs($seo)->withSession(verifiedAdminSession($seo));
    $this->get(route('admin.redirects.index'))->assertOk();
});

it('serves configured one-hop redirects and 410 responses with atomic hit counters', function () {
    $this->post(route('admin.redirects.store'), ['from_path' => '/legacy-a', 'to_url' => '/legacy-b', 'status_code' => 301]);
    $this->post(route('admin.redirects.store'), ['from_path' => '/legacy-b', 'to_url' => '/destination', 'status_code' => 302]);
    $this->get('/legacy-a')->assertStatus(301)->assertRedirect('/destination');
    $this->get('/legacy-a')->assertStatus(301);
    $rule = Redirect::query()->where('from_path', '/legacy-a')->first();
    expect((int) $rule->hits)->toBe(2)->and($rule->last_hit_at)->not->toBeNull();
    $this->post(route('admin.redirects.store'), ['from_path' => '/removed-resource', 'status_code' => 410]);
    $this->get('/removed-resource')->assertStatus(410)->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    expect(NotFoundLog::query()->count())->toBe(0);
});
