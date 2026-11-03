<?php

use Illuminate\Support\Facades\Route;

it('sets security and staging indexing headers', function () {
    $this->get('/up')
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Content-Security-Policy-Report-Only');
});

it('uses a different CSP nonce for each request', function () {
    $first = $this->get('/up')->headers->get('Content-Security-Policy-Report-Only');
    $second = $this->get('/up')->headers->get('Content-Security-Policy-Report-Only');
    expect($first)->not->toBe($second);
});

it('restricts framing and sessions on the configured admin path', function () {
    config(['trivo.admin_path' => 'hq-test']);
    Route::middleware('web')->get('/hq-test/probe', fn () => response()->json([
        'same_site' => config('session.same_site'), 'lifetime' => config('session.lifetime'),
    ]));
    $this->get('/hq-test/probe')->assertOk()->assertHeader('X-Frame-Options', 'DENY')
        ->assertJson(['same_site' => 'strict', 'lifetime' => 20]);
    expect(config('session.same_site'))->toBe('lax');
});

it('does not expose legacy WordPress entrypoints', function (string $path) {
    $this->get($path)->assertNotFound()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
})->with(['/wp-admin', '/wp-login.php', '/xmlrpc.php']);

it('does not serve a placeholder as the migrated homepage', function () {
    $this->get('/')->assertStatus(503)->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});

it('does not expose the framework private-file serving or upload routes', function () {
    $this->get('/storage/private.txt')->assertNotFound();
    $this->put('/storage/private.txt')->assertNotFound();
});
