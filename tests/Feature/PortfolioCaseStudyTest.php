<?php

use App\Models\Media;
use App\Models\Page;
use App\Models\PortfolioItem;
use App\Models\Post;
use App\Models\Revision;
use Database\Seeders\LuhaifPortfolioSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->seed(RolePermissionSeeder::class);
    $this->editor = adminAccount('Editor');
    $this->actingAs($this->editor)->withSession(verifiedAdminSession($this->editor));
});

function caseStudyPayload(PortfolioItem $portfolio, ?array $layout = null): array
{
    return ['title' => $portfolio->title, 'path' => $portfolio->path, 'sort' => 0,
        'content_version' => $portfolio->content_version, 'blocks_json' => '[]',
        'case_study_json' => json_encode($layout ?? $portfolio->details['case_study'])];
}

it('creates new visual profiles from admin and keeps their design editable after publishing', function () {
    $this->seed(LuhaifPortfolioSeeder::class);
    $this->get(route('admin.portfolio.create'))->assertOk()->assertSee('Case-study design and content')
        ->assertSee('name="case_study_json"', false);
    $layout = app(\App\Services\Content\PortfolioLayout::class)->blank();
    $layout['thumbnail_media_id'] = Media::query()->firstOrFail()->id;
    $layout['sections'][0]['title'] = 'New profile hero';
    $layout['sections'][0]['body'] = '<p>New profile introduction.</p>';
    $new = new PortfolioItem(['title' => 'New admin profile', 'path' => '/new-admin-profile', 'content_version' => 1,
        'details' => ['case_study' => $layout]]);
    $this->post(route('admin.portfolio.store'), caseStudyPayload($new))->assertSessionHasNoErrors()->assertRedirect();
    $profile = PortfolioItem::query()->where('path', '/new-admin-profile')->sole();
    expect($profile->status)->toBe('draft')->and($profile->details['case_study']['sections'])->toHaveCount(4);
    $this->get(route('admin.portfolio.edit', $profile))->assertOk()->assertSee('New profile hero');
    $this->post(route('admin.portfolio.store'), caseStudyPayload($new))->assertSessionHasErrors('path');
    $profile->update(['seo_title' => 'New profile SEO', 'seo_description' => 'New profile description', 'robots' => 'index,follow']);
    $this->post(route('admin.portfolio.publication', $profile), ['operation' => 'publish', 'content_version' => 1])
        ->assertSessionHasNoErrors();
    $this->get('/new-admin-profile')->assertOk()->assertSee('New profile hero')->assertSee('New profile introduction.');
    $profile->refresh();
    $layout['sections'][1]['title'] = 'Edited new profile report';
    $this->put(route('admin.portfolio.update', $profile), caseStudyPayload($profile, $layout))->assertSessionHasNoErrors();
    $this->get('/new-admin-profile')->assertOk()->assertSee('Edited new profile report');
    $this->get('/portfolio')->assertOk()->assertSee('New admin profile');
    expect($profile->fresh()->status)->toBe('published')->and($profile->fresh()->content_version)->toBe(3);
});

it('imports only Luhaif with original images and retains the source post and URL history', function () {
    $source = Post::query()->create(['title' => 'Luhaif Perfumes', 'slug' => 'luhaif-perfumes',
        'path' => '/luhaif-perfumes', 'body' => 'Original retained source', 'status' => 'published', 'published_at' => now()->subDay()]);
    $this->seed(LuhaifPortfolioSeeder::class);
    $portfolio = PortfolioItem::query()->sole();
    expect($portfolio->path)->toBe('/luhaif-perfumes')->and($portfolio->status)->toBe('published')
        ->and($portfolio->details['case_study']['sections'])->toHaveCount(9)
        ->and($portfolio->details['case_study']['sections'][0]['body'])->toContain('Trivo’s')
        ->and($source->fresh()->body)->toBe('Original retained source')->and($source->fresh()->status)->toBe('draft')
        ->and($source->fresh()->path)->toBe('/portfolio-source/luhaif-perfumes-'.$source->id);
    expect(Revision::query()->where('snapshot->kind', 'portfolio_source_transfer')->sole()->snapshot['source']['path'])->toBe('/luhaif-perfumes');
    $this->get('/luhaif-perfumes')->assertOk()->assertSee('case-section--hero', false)
        ->assertSee('Facebook Growth')->assertSee('Instagram Content Overview')->assertSee('110%')->assertSee('250%')
        ->assertSee('Luhaif-Thumbnail.png')->assertSee('property="og:title"', false)->assertDontSee('Original retained source');
});

it('does not overwrite subsequent edits or duplicate imported media on reseeding', function () {
    $this->seed(LuhaifPortfolioSeeder::class);
    $portfolio = PortfolioItem::query()->sole();
    $portfolio->update(['title' => 'Admin edited title']);
    $mediaCount = Media::query()->count();
    $this->seed(LuhaifPortfolioSeeder::class);
    expect(PortfolioItem::query()->sole()->title)->toBe('Admin edited title')
        ->and(Media::query()->count())->toBe($mediaCount)->and(Revision::query()->count())->toBe(1);
});

it('edits published sections and design with revisions stale protection and dynamic listing cards', function () {
    $this->seed(LuhaifPortfolioSeeder::class);
    $portfolio = PortfolioItem::query()->sole();
    $original = Revision::query()->sole();
    $this->get(route('admin.portfolio.edit', $portfolio))->assertOk()->assertSee('Case-study design and content')->assertSee('Save published portfolio');
    $layout = $portfolio->details['case_study'];
    $layout['sections'][1]['title'] = 'Updated Facebook report';
    $layout['sections'][1]['body'] = '<p>Edited in admin <strong>bold text</strong><script>alert(1)</script></p>';
    $layout['background'] = '#f0f0ff';
    $layout['display_date'] = '2026-06';
    $this->put(route('admin.portfolio.update', $portfolio), [...caseStudyPayload($portfolio, $layout), 'title' => 'Luhaif admin title'])
        ->assertSessionHasNoErrors()->assertRedirect();
    $portfolio->refresh();
    expect($portfolio->status)->toBe('published')->and($portfolio->content_version)->toBe(2)
        ->and($portfolio->details['case_study']['sections'][1]['body'])->not->toContain('<script>');
    $this->get('/luhaif-perfumes')->assertOk()->assertSee('Updated Facebook report')->assertSee('Edited in admin')
        ->assertSee('--case-bg: #f0f0ff', false)->assertDontSee('alert(1)');
    $this->get('/portfolio')->assertOk()->assertSee('Luhaif admin title')->assertSee('June 2026');
    $this->put(route('admin.portfolio.update', $portfolio), [...caseStudyPayload($portfolio), 'content_version' => 1])->assertSessionHasErrors('content_version');
    $this->put(route('admin.portfolio.update', $portfolio), [...caseStudyPayload($portfolio), 'path' => '/renamed'])->assertSessionHasErrors('path');
    $this->delete(route('admin.portfolio.archive', $portfolio), ['content_version' => 2])->assertForbidden();
    $this->post(route('admin.portfolio.revision', [$portfolio, $original]), ['content_version' => 2])->assertSessionHasNoErrors();
    $this->get('/luhaif-perfumes')->assertOk()->assertSee('Facebook Growth')->assertDontSee('Updated Facebook report');
    expect($portfolio->fresh()->status)->toBe('published')->and($portfolio->fresh()->content_version)->toBe(3);
});

it('validates image references layout values and links without saving unsafe input', function (string $field, mixed $value) {
    $this->seed(LuhaifPortfolioSeeder::class);
    $portfolio = PortfolioItem::query()->sole();
    $layout = $portfolio->details['case_study'];
    data_set($layout, $field, $value);
    $this->put(route('admin.portfolio.update', $portfolio), caseStudyPayload($portfolio, $layout))->assertSessionHasErrors();
    expect($portfolio->fresh()->content_version)->toBe(1);
})->with([
    ['sections.0.button_url', 'javascript:alert(1)'], ['sections.0.button_url', '//evil.test'],
    ['sections.0.image_id', 999999], ['background', 'red;display:none'],
    ['sections.0.padding_top', -1], ['sections.0.type', 'raw_html'],
]);

it('honours visibility ordering roles and public publication state', function () {
    $this->seed(LuhaifPortfolioSeeder::class);
    $portfolio = PortfolioItem::query()->sole();
    $layout = $portfolio->details['case_study'];
    $layout['sections'][1]['visible'] = false;
    [$layout['sections'][2], $layout['sections'][3]] = [$layout['sections'][3], $layout['sections'][2]];
    $this->put(route('admin.portfolio.update', $portfolio), caseStudyPayload($portfolio, $layout))->assertSessionHasNoErrors();
    $this->get('/luhaif-perfumes')->assertDontSee('<h2>Facebook Growth</h2>', false)
        ->assertSeeInOrder(['<h2>Facebook Content Overview</h2>', '<h2>Instagram Growth</h2>'], false);
    $seo = adminAccount('SEO Manager');
    $this->actingAs($seo)->withSession(verifiedAdminSession($seo));
    $this->put(route('admin.portfolio.update', $portfolio), caseStudyPayload($portfolio->fresh()))->assertForbidden();
    $portfolio->update(['status' => 'draft']);
    $this->get('/luhaif-perfumes')->assertNotFound();
    $portfolio->forceFill(['status' => 'published', 'published_at' => now()->addDay()])->save();
    $this->get('/luhaif-perfumes')->assertNotFound();
});

it('stops import without touching unrelated content when the URL is already owned', function () {
    Page::query()->create(['title' => 'Existing page', 'path' => '/luhaif-perfumes', 'status' => 'published']);
    expect(fn () => $this->seed(LuhaifPortfolioSeeder::class))->toThrow(RuntimeException::class);
    expect(PortfolioItem::query()->count())->toBe(0)->and(Media::query()->count())->toBe(0);
});
