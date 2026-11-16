<?php

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->seed(RolePermissionSeeder::class);
});

it('renders an accessible private sign-in page without public assets', function () {
    $this->get(route('admin.login'))->assertOk()->assertSee('Email address')
        ->assertSee('autocomplete="current-password"', false)->assertSee('csrf-token')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertHeader('Cache-Control', 'no-store, private');
});

it('requires sign-in before all protected admin screens', function (string $route) {
    $this->get(route($route))->assertRedirect(route('admin.login'));
})->with(['admin.dashboard', 'admin.styleguide', 'admin.activity']);

it('authenticates with email and password without an authenticator', function (bool $enrolled) {
    $user = adminAccount(enrolled: $enrolled);
    $this->post(route('admin.authenticate'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('admin.verified_user_id', $user->id)
        ->assertSessionMissing('admin.pending');
    $this->assertAuthenticatedAs($user);
    $this->get(route('admin.dashboard'))->assertOk()->assertSee('Average SEO score');
    expect($user->fresh()->last_login_at)->not->toBeNull();
    expect(Activity::query()->where('event', 'login_succeeded')->count())->toBe(1);
    $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));
    $this->assertGuest();
})->with([true, false]);

it('rejects incorrect passwords', function () {
    $user = adminAccount(enrolled: false);
    $this->post(route('admin.authenticate'), ['email' => $user->email, 'password' => 'incorrect'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('rejects disabled accounts without disclosing account existence', function () {
    $user = adminAccount();
    $user->forceFill(['is_active' => false])->save();
    $this->post(route('admin.authenticate'), ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('denies a forged authenticated session without the admin sign-in marker', function () {
    $user = adminAccount();
    $this->actingAs($user)->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    $this->assertGuest();
});

it('expires idle sessions and revokes outdated auth versions', function () {
    $user = adminAccount();
    $this->actingAs($user)->withSession(array_replace(verifiedAdminSession($user), ['admin.last_activity' => now()->timestamp - 1201]))
        ->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    $this->actingAs($user)->withSession(array_replace(verifiedAdminSession($user), ['admin.auth_version' => 0]))
        ->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
});

it('restricts the styleguide and activity log by role', function () {
    $user = adminAccount('Editor');
    $this->actingAs($user)->withSession(verifiedAdminSession($user));
    $this->get(route('admin.dashboard'))->assertOk()->assertDontSee('Enquiries')->assertDontSee('Recorded 404 hits');
    $this->get(route('admin.styleguide'))->assertForbidden();
    $this->get(route('admin.activity'))->assertForbidden();
});

it('renders shared components for a fully verified Super Admin', function () {
    $user = adminAccount();
    $this->actingAs($user)->withSession(verifiedAdminSession($user));
    $this->get(route('admin.styleguide'))->assertOk()->assertSee('Shared admin components')->assertSee('<dialog', false);
    $this->get(route('admin.activity'))->assertOk();
});

it('persists a validated personal theme preference and rejects open redirects', function () {
    $user = adminAccount();
    $this->actingAs($user)->withSession(verifiedAdminSession($user));
    $this->patch(route('admin.preferences'), ['theme' => 'dark', 'return_to' => 'admin.dashboard'])->assertRedirect(route('admin.dashboard'));
    expect($user->fresh()->theme)->toBe('dark');
    $this->patch(route('admin.preferences'), ['theme' => 'light', 'return_to' => 'https://evil.test'])->assertSessionHasErrors('return_to');
    expect($user->fresh()->theme)->toBe('dark');
});

it('honors an optional admin IP allowlist', function () {
    config(['trivo.admin_allowed_ips' => ['192.0.2.10']]);
    $this->get(route('admin.login'))->assertNotFound();
});

it('rate limits repeated sign-in attempts', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post(route('admin.authenticate'), ['email' => 'missing@example.test', 'password' => 'invalid'])->assertSessionHasErrors('email');
    }
    $this->post(route('admin.authenticate'), ['email' => 'missing@example.test', 'password' => 'invalid'])->assertStatus(429);
});

it('requires CSRF tokens for sign-in and authenticated preference changes', function () {
    app()->instance('env', 'local'); // Exercise CSRF instead of Laravel's test-environment bypass.
    $this->post(route('admin.authenticate'), ['email' => 'test@example.test', 'password' => 'invalid'])->assertStatus(419);
    $user = adminAccount();
    $this->actingAs($user)->withSession(verifiedAdminSession($user));
    $this->patch(route('admin.preferences'), ['theme' => 'dark', 'return_to' => 'admin.dashboard'])->assertStatus(419);
    expect($user->fresh()->theme)->toBe('system');
});

it('keeps the styleguide disabled in production', function () {
    $user = adminAccount();
    $this->actingAs($user)->withSession(verifiedAdminSession($user));
    app()->instance('env', 'production');
    $this->get(route('admin.styleguide'))->assertForbidden();
});
