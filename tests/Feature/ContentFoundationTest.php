<?php

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('preserves full legacy paths and original dates', function () {
    $page = Page::create([
        'legacy_wp_id' => 19, 'title' => 'Original title', 'path' => '/services/legacy-path',
        'status' => 'published', 'published_at' => '2023-05-01 08:30:00',
        'seo_title' => 'Preserved SEO title', 'schema_json' => ['@type' => 'WebPage'],
    ]);
    // Importers set historical timestamps explicitly; editors cannot mass-assign them.
    $page->forceFill(['created_at' => '2023-05-01 08:30:00', 'updated_at' => '2025-01-10 12:00:00'])->save();
    $page->refresh();
    expect($page->path)->toBe('/services/legacy-path')
        ->and($page->published_at->format('Y-m-d H:i:s'))->toBe('2023-05-01 08:30:00')
        ->and($page->created_at->format('Y-m-d H:i:s'))->toBe('2023-05-01 08:30:00')
        ->and($page->updated_at->format('Y-m-d H:i:s'))->toBe('2025-01-10 12:00:00')
        ->and($page->seo_title)->toBe('Preserved SEO title')
        ->and($page->schema_json)->toBe(['@type' => 'WebPage']);
});

it('rejects two pages with the same full path', function () {
    Page::create(['title' => 'One', 'path' => '/same-path']);
    Page::create(['title' => 'Two', 'path' => '/same-path']);
})->throws(QueryException::class);

it('encrypts SMTP settings at rest and hides values from serialization', function () {
    $setting = Setting::create(['key' => 'smtp', 'group' => 'mail', 'value' => ['password' => 'test-only-secret']]);
    $raw = DB::table('settings')->where('id', $setting->id)->value('value');
    expect($raw)->not->toContain('test-only-secret')
        ->and($setting->fresh()->value['password'])->toBe('test-only-secret')
        ->and($setting->toArray())->not->toHaveKey('value');
});
