<?php

use App\Actions\Admin\ArchiveMedia;
use App\Actions\Admin\SaveWebsiteSettings;
use App\Models\Media;
use App\Models\Setting;
use App\Services\Settings\WebsiteSettings;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->seed(RolePermissionSeeder::class);
    $this->admin = adminAccount();
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
});

it('shows empty forms without creating settings or inventing website details', function (string $section) {
    $this->get(route('admin.website.edit', $section))->assertOk()->assertSee('Save '.$section.' settings');
    expect(Setting::query()->count())->toBe(0);
})->with(['site', 'social', 'analytics']);

it('saves encrypted plain text and only the fields for the selected group', function () {
    $this->put(route('admin.website.update', 'site'), ['site_name' => 'Fixture & Company', 'address' => "Line one\nLine two",
        'footer_text' => 'Fixture copyright', 'contact_email' => 'hello@example.test', 'phone' => '+91 (123) 456-7890',
        'whatsapp' => '+911234567890', 'key' => 'mail.smtp', 'group' => 'mail', 'password' => 'injected-secret', 'ga4_id' => 'G-INJECTED'])
        ->assertRedirect(route('admin.website.edit', 'site'))->assertSessionHasNoErrors();
    $setting = Setting::query()->sole();
    expect($setting->key)->toBe('website.site')->and($setting->group)->toBe('site')
        ->and($setting->value['address'])->toBe("Line one\nLine two")->and($setting->value)->not->toHaveKeys(['password', 'ga4_id'])
        ->and(DB::table('settings')->value('value'))->not->toContain('Fixture & Company')
        ->and($setting->toArray())->not->toHaveKey('value');
    $this->get(route('admin.website.edit', 'site'))->assertSee('Fixture &amp; Company', false)->assertSee("Line one\nLine two");
    expect(Activity::query()->latest('id')->first()->event)->toBe('website_site_updated')
        ->and(Activity::query()->get()->toJson())->not->toContain('hello@example.test');
});

it('rejects HTML and control characters before storing any values', function (array $values, string $field) {
    $this->put(route('admin.website.update', 'site'), array_replace(['site_name' => 'Fixture'], $values))->assertSessionHasErrors($field);
    expect(Setting::query()->count())->toBe(0);
})->with([
    [['site_name' => '<script>alert(1)</script>'], 'site_name'],
    [['contact_email' => "a@example.test\r\nBcc:bad@example.test"], 'contact_email'],
    [['phone' => 'tel:alert(1)'], 'phone'],
    [['whatsapp' => '0123'], 'whatsapp'],
    [['footer_text' => '<img src=x onerror=alert(1)>'], 'footer_text'],
    [['site_name' => ['malformed']], 'site_name'],
]);

it('accepts HTTPS social URLs and explicitly clears removed profiles', function () {
    $this->put(route('admin.website.update', 'social'), ['linkedin' => 'https://www.linkedin.com/company/fixture', 'x' => 'https://x.com/fixture'])->assertSessionHasNoErrors();
    $this->put(route('admin.website.update', 'social'), ['linkedin' => ''])->assertSessionHasNoErrors();
    expect(app(WebsiteSettings::class)->read('social')['linkedin'])->toBeNull()
        ->and(app(WebsiteSettings::class)->read('social')['x'])->toBeNull();
});

it('rejects an array submitted in place of a profile URL', function () {
    $this->put(route('admin.website.update', 'social'), ['facebook' => ['malformed']])->assertSessionHasErrors('facebook');
    expect(Setting::query()->count())->toBe(0);
});

it('rejects executable insecure and credential-bearing social URLs', function (string $url) {
    $this->put(route('admin.website.update', 'social'), ['facebook' => $url])->assertSessionHasErrors('facebook');
    expect(Setting::query()->count())->toBe(0);
})->with(['javascript:alert(1)', 'http://example.test/profile', '//example.test/profile', 'https://user:secret@example.test/profile', 'https://example.test/" onclick="alert(1)']);

it('stores analytics identifiers without scripts or cross-group writes', function () {
    $this->put(route('admin.website.update', 'analytics'), ['enabled' => '1', 'ga4_id' => 'G-TEST123456', 'gtm_id' => 'GTM-TEST123',
        'meta_pixel_id' => '123456789012345', 'google_verification' => 'fixture_GOOGLE-token', 'bing_verification' => 'ABC123',
        'header_scripts' => '<script>bad()</script>', 'site_name' => 'Overridden'])->assertSessionHasNoErrors();
    $data = app(WebsiteSettings::class)->read('analytics');
    expect($data['enabled'])->toBeTrue()->and($data['ga4_id'])->toBe('G-TEST123456')->and($data)->not->toHaveKeys(['header_scripts', 'site_name']);
    $this->get(route('admin.website.edit', 'analytics'))->assertDontSee('<script>bad()', false)->assertSee('Tracking remains inactive');
});

it('requires a valid tracker when enabled and rejects code in tokens', function (array $values, string $field) {
    $this->put(route('admin.website.update', 'analytics'), array_replace(['enabled' => false], $values))->assertSessionHasErrors($field);
    expect(Setting::query()->count())->toBe(0);
})->with([
    [['enabled' => true], 'ga4_id'], [['ga4_id' => 'UA-12345-1'], 'ga4_id'], [['gtm_id' => 'GTM-X";alert(1)'], 'gtm_id'],
    [['meta_pixel_id' => 'abc123'], 'meta_pixel_id'], [['google_verification' => '<meta content="token">'], 'google_verification'],
]);

it('protects ready branding images from archive until settings release them', function () {
    $image = Media::query()->create(['disk' => 'public', 'path' => 'media/11111111-1111-4111-8111-111111111111/original.png',
        'original_name' => 'fixture.png', 'mime' => 'image/png', 'width' => 96, 'height' => 96, 'size' => 100, 'sha256' => hash('sha256', 'fixture'), 'alt' => 'Fixture logo', 'processing_status' => 'ready']);
    $this->put(route('admin.website.update', 'site'), ['site_name' => 'Fixture', 'logo_media_id' => $image->id, 'favicon_media_id' => $image->id])->assertSessionHasNoErrors();
    expect(fn () => app(ArchiveMedia::class)->handle($image))->toThrow(ValidationException::class);
    $this->put(route('admin.website.update', 'site'), ['site_name' => 'Fixture', 'logo_media_id' => ''])->assertSessionHasNoErrors();
    app(ArchiveMedia::class)->handle($image);
    expect($image->fresh()->trashed())->toBeTrue();
    $this->put(route('admin.website.update', 'site'), ['site_name' => 'Fixture', 'logo_media_id' => $image->id])->assertSessionHasErrors('logo_media_id');
});

it('rechecks media state and rejects non-square icons and unsafe image formats', function () {
    $image = Media::query()->create(['disk' => 'public', 'path' => 'media/fixture/original.png', 'original_name' => 'fixture.png',
        'mime' => 'image/png', 'width' => 100, 'height' => 50, 'size' => 100, 'sha256' => hash('sha256', 'fixture'), 'alt' => 'Fixture', 'processing_status' => 'ready']);
    $this->put(route('admin.website.update', 'site'), ['site_name' => 'Fixture', 'favicon_media_id' => $image->id])->assertSessionHasErrors('favicon_media_id');
    $image->update(['mime' => 'image/svg+xml']);
    $this->put(route('admin.website.update', 'site'), ['site_name' => 'Fixture', 'logo_media_id' => $image->id])->assertSessionHasErrors('logo_media_id');
    $image->update(['mime' => 'image/png', 'processing_status' => 'queued']);
    expect(fn () => app(SaveWebsiteSettings::class)->handle('site', ['site_name' => 'Fixture', 'logo_media_id' => $image->id]))->toThrow(ValidationException::class);
    expect(Setting::query()->count())->toBe(0);
});

it('enforces the site versus SEO permission boundary', function (string $role, string $section, bool $allowed) {
    $user = adminAccount($role);
    $this->actingAs($user)->withSession(verifiedAdminSession($user));
    $this->get(route('admin.website.edit', $section))->assertStatus($allowed ? 200 : 403);
    $this->put(route('admin.website.update', $section), $section === 'analytics' ? ['enabled' => false] : ['site_name' => 'Fixture'])
        ->assertStatus($allowed ? 302 : 403);
})->with([
    ['Editor', 'site', false], ['Editor', 'social', false], ['Editor', 'analytics', false],
    ['SEO Manager', 'site', false], ['SEO Manager', 'social', false], ['SEO Manager', 'analytics', true],
    ['Admin', 'site', true], ['Admin', 'social', true], ['Admin', 'analytics', true],
]);

it('requires CSRF and never accepts arbitrary settings groups', function () {
    $this->get(route('admin.website.edit', 'mail.smtp'))->assertNotFound();
    $this->app['env'] = 'local';
    $this->put(route('admin.website.update', 'site'), ['site_name' => 'Fixture'])->assertStatus(419);
    expect(Setting::query()->count())->toBe(0);
});

it('rejects guests and sessions without a verified second factor', function () {
    auth()->logout();
    $this->flushSession();
    $this->get(route('admin.website.edit', 'site'))->assertRedirect(route('admin.login'));
    $this->put(route('admin.website.update', 'site'), ['site_name' => 'Fixture'])->assertRedirect(route('admin.login'));
    $this->actingAs($this->admin);
    $this->get(route('admin.website.edit', 'analytics'))->assertRedirect(route('admin.login'));
    expect(Setting::query()->count())->toBe(0);
});

it('escapes imported values on output even if they bypass the form rules', function () {
    Setting::query()->create(['key' => 'website.site', 'group' => 'site', 'value' => ['site_name' => '"><script>bad()</script>', 'footer_text' => '<img src=x onerror=bad()>']]);
    $this->get(route('admin.website.edit', 'site'))->assertOk()->assertDontSee('<script>bad()', false)->assertDontSee('<img src=x', false)->assertSee('&lt;script&gt;bad()', false);
});

it('preserves the current settings screen after a theme change with validated parameters', function () {
    $this->patch(route('admin.preferences'), ['theme' => 'dark', 'return_to' => 'admin.website.edit', 'return_section' => 'social'])
        ->assertRedirect(route('admin.website.edit', 'social'));
    $this->patch(route('admin.preferences'), ['theme' => 'dark', 'return_to' => 'admin.website.edit', 'return_section' => 'https://evil.test'])
        ->assertSessionHasErrors('return_section');
});
