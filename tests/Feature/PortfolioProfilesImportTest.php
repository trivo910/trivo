<?php

use App\Models\Media;
use App\Models\PortfolioItem;
use App\Models\Post;
use App\Models\Revision;
use Database\Seeders\PortfolioProfilesSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    $this->seed(RolePermissionSeeder::class);
    $editor = adminAccount('Editor');
    $this->actingAs($editor)->withSession(verifiedAdminSession($editor));
});

function portfolioReferenceManifest(): array
{
    return json_decode(file_get_contents(database_path('seeders/data/portfolio-profiles.json')), true, 512, JSON_THROW_ON_ERROR);
}

it('imports every reference profile and renders all original URLs and database listing cards', function () {
    foreach (portfolioReferenceManifest() as $entry) {
        $data = json_decode(file_get_contents(database_path('seeders/data/'.$entry['file'])), true);
        Post::query()->create(['title' => $data['source_titles'][0] ?? $data['title'], 'path' => $data['path'],
            'slug' => ltrim($data['path'], '/'), 'body' => 'Retained original source', 'status' => 'published', 'published_at' => now()->subDay()]);
    }
    $this->seed(PortfolioProfilesSeeder::class);
    expect(PortfolioItem::query()->count())->toBe(14)->and(Post::query()->where('status', 'published')->count())->toBe(0);
    foreach (portfolioReferenceManifest() as $entry) {
        $profile = PortfolioItem::query()->where('path', $entry['path'])->sole();
        $this->get($entry['path'])->assertOk()->assertSee('class="case-study', false);
        $this->get(route('admin.portfolio.edit', $profile))->assertOk()->assertSee('Save published portfolio')
            ->assertSee('Case-study design and content');
        $source = Post::query()->findOrFail($profile->details['source']['post_id']);
        expect($source->body)->toBe('Retained original source')->and($source->path)->toStartWith('/portfolio-source/');
    }
    $listing = $this->get('/portfolio')->assertOk();
    expect(substr_count($listing->getContent(), '<article class="portfolio-project">'))->toBe(14);
    expect(Media::query()->where('processing_status', '!=', 'ready')->count())->toBe(0);
});

it('saves and restores every profiles content without losing its design or publication state', function () {
    $this->seed(PortfolioProfilesSeeder::class);
    foreach (PortfolioItem::query()->get() as $profile) {
        $original = Revision::query()->where('revisionable_type', PortfolioItem::class)->where('revisionable_id', $profile->id)->sole();
        $layout = $profile->details['case_study'];
        $family = $layout['design'] ?? 'modern';
        $text = 'Verified profile edit '.$profile->id;
        $layout['sections'][0]['body'] = '<p>'.$text.'</p>';
        $this->put(route('admin.portfolio.update', $profile), ['title' => $profile->title, 'path' => $profile->path,
            'sort' => $profile->sort, 'blocks_json' => '[]', 'case_study_json' => json_encode($layout), 'content_version' => 1])
            ->assertSessionHasNoErrors();
        $this->get($profile->path)->assertOk()->assertSee($text);
        $profile->refresh();
        expect($profile->details['case_study']['design'] ?? 'modern')->toBe($family)->and($profile->status)->toBe('published');
        $this->post(route('admin.portfolio.revision', [$profile, $original]), ['content_version' => 2])->assertSessionHasNoErrors();
        $this->get($profile->path)->assertOk()->assertDontSee($text);
    }
});

it('retains profile specific sections metrics tables and testimonial content', function () {
    $this->seed(PortfolioProfilesSeeder::class);
    $this->get('/sell-sold')->assertOk()->assertSee('Reports Snapshot Dashboard')->assertSee('150%');
    $this->get('/mumbai-darshan')->assertOk()->assertSee('185K+')->assertSee('Google Ads');
    $this->get('/arihant-panel-fittings')->assertOk()->assertSee('case-section--services', false)
        ->assertSee('case-section--gallery', false)->assertSee('case-section--testimonial', false)->assertSee('220%');
    $this->get('/stocksovet')->assertOk()->assertSee('178%')->assertSee('case-section--impact', false);
    $this->get('/al-bushra-t-and-t')->assertOk()->assertSee('<table', false)->assertSee('277')->assertSee('Project Goals:');
    $this->get('/miftahi-shop')->assertOk()->assertSee('14.6 k followers')->assertDontSee('Business impact');
});

it('reruns the full import without duplicating media revisions or overwriting admin edits', function () {
    $this->seed(PortfolioProfilesSeeder::class);
    $profile = PortfolioItem::query()->where('path', '/mumbai-darshan')->sole();
    $profile->update(['title' => 'Admin retained Mumbai title']);
    $media = Media::query()->count();
    $revisions = Revision::query()->count();
    $this->seed(PortfolioProfilesSeeder::class);
    expect(PortfolioItem::query()->count())->toBe(14)->and($profile->fresh()->title)->toBe('Admin retained Mumbai title')
        ->and(Media::query()->count())->toBe($media)->and(Revision::query()->count())->toBe($revisions);
});
