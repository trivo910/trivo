<?php

use App\Jobs\ProcessMedia;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Revision;
use App\Models\Setting;
use App\Services\Media\ImageProcessor;
use App\Services\Media\MediaPaths;
use App\Services\Media\MediaUsage;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    Storage::fake('public');
    Storage::fake('local');
    Bus::fake([ProcessMedia::class]);
    $this->seed(RolePermissionSeeder::class);
    $this->admin = adminAccount('Editor');
    $this->actingAs($this->admin)->withSession(verifiedAdminSession($this->admin));
});

it('stages a validated upload privately and queues only its record ID', function () {
    $this->postJson(route('admin.media.store'), ['image' => UploadedFile::fake()->image('photo.png', 32, 24),
        'alt' => 'Synthetic test image', 'folder' => 'Blog'])->assertOk()->assertJsonPath('status', 'queued');
    $media = Media::query()->sole();
    expect($media->path)->toMatch('/^media\/[a-f0-9-]{36}\/original\.png$/')->and($media->processing_status)->toBe('queued');
    Storage::disk('public')->assertMissing($media->path);
    Storage::disk('local')->assertExists(app(MediaPaths::class)->staging($media).'/upload.bin');
    Bus::assertDispatched(ProcessMedia::class, fn (ProcessMedia $job) => $job->mediaId === $media->id && $job->connection === 'database');
});

it('reencodes strips metadata generates bounded WebP AVIF variants and renders srcsets', function () {
    $file = UploadedFile::fake()->image('landscape.png', 1920, 1080);
    $bytes = $file->getContent();
    $text = "Comment\0PRIVATE-METADATA-FIXTURE";
    $chunk = pack('N', strlen($text)).'tEXt'.$text.pack('N', crc32('tEXt'.$text));
    $file = UploadedFile::fake()->createWithContent('landscape.png', substr($bytes, 0, -12).$chunk.substr($bytes, -12));
    $this->post(route('admin.media.store'), ['image' => $file, 'alt' => 'Synthetic landscape'])->assertRedirect();
    $media = Media::query()->sole();
    app(ImageProcessor::class)->process($media->id);
    $media->refresh();
    expect($media->processing_status)->toBe('ready')->and($media->variants)->toHaveCount(8);
    $master = Storage::disk('public')->get($media->path);
    expect($master)->not->toContain('PRIVATE-METADATA-FIXTURE')->and(hash('sha256', $master))->toBe($media->sha256);
    foreach ($media->variants as $variant) {
        $info = getimagesize(Storage::disk('public')->path($variant['path']));
        expect($info['mime'])->toBe($variant['mime'])->and($info[0])->toBe($variant['width'])
            ->and($variant['width'])->toBeLessThanOrEqual(1920);
    }
    Storage::disk('local')->assertMissing(app(MediaPaths::class)->staging($media).'/upload.bin');
    $html = Blade::render('<x-media.picture :media="$media" :priority="true" sizes="50vw" />', compact('media'));
    expect($html)->toContain('image/avif', 'image/webp', '480w', '1920w', 'fetchpriority="high"', 'alt="Synthetic landscape"');
    app(ImageProcessor::class)->process($media->id);
    expect(Storage::disk('public')->get($media->path))->toBe($master);
});

it('does not upscale small uploads', function () {
    $this->post(route('admin.media.store'), ['image' => UploadedFile::fake()->image('tiny.jpg', 32, 24), 'alt' => 'Small synthetic photo']);
    $media = Media::query()->sole();
    app(ImageProcessor::class)->process($media->id);
    expect($media->fresh()->variants)->toHaveCount(2);
    foreach ($media->fresh()->variants as $variant) {
        expect($variant['width'])->toBe(32)->and($variant['height'])->toBe(24);
    }
});

it('rejects unsupported executable vector and video uploads', function (string $filename, string $content) {
    $this->post(route('admin.media.store'), ['image' => UploadedFile::fake()->createWithContent($filename, $content), 'alt' => 'Test'])
        ->assertSessionHasErrors('image');
    expect(Media::query()->count())->toBe(0);
    expect(Storage::disk('public')->allFiles())->toBe([]);
})->with([
    ['payload.php', '<?php echo 1;'], ['vector.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'],
    ['video.mp4', 'synthetic-video'], ['report.pdf', '%PDF-1.7'],
]);

it('rejects MIME mismatches double extensions and appended PHP polyglots', function () {
    $png = UploadedFile::fake()->image('safe.png', 32, 24)->getContent();
    foreach (['wrong.jpg' => $png, 'payload.php.png' => $png, 'polyglot.png' => $png.'<?php echo 1;'] as $name => $content) {
        $this->post(route('admin.media.store'), ['image' => UploadedFile::fake()->createWithContent($name, $content), 'alt' => 'Test'])->assertSessionHasErrors('image');
    }
    expect(Media::query()->count())->toBe(0);
});

it('enforces byte and pixel limits before decoding', function () {
    $this->post(route('admin.media.store'), ['image' => UploadedFile::fake()->create('huge.png', 8193, 'image/png'), 'alt' => 'Test'])->assertSessionHasErrors('image');
    config(['media.max_pixels' => 100]);
    $this->post(route('admin.media.store'), ['image' => UploadedFile::fake()->image('pixels.png', 32, 24), 'alt' => 'Test'])->assertSessionHasErrors('image');
    expect(Media::query()->count())->toBe(0);
});

it('requires alt text and treats folders as virtual labels', function () {
    $this->post(route('admin.media.store'), ['image' => UploadedFile::fake()->image('photo.png', 32, 24), 'folder' => '../outside'])
        ->assertSessionHasErrors(['alt', 'folder']);
});

it('reuses duplicate files without overwriting metadata or dispatching a second job', function () {
    $bytes = UploadedFile::fake()->image('photo.png', 32, 24)->getContent();
    foreach (['Original description', 'Different description'] as $alt) {
        $this->post(route('admin.media.store'), ['image' => UploadedFile::fake()->createWithContent('photo.png', $bytes), 'alt' => $alt])->assertRedirect();
    }
    expect(Media::query()->count())->toBe(1)->and(Media::query()->sole()->alt)->toBe('Original description');
    Bus::assertDispatchedTimes(ProcessMedia::class, 1);
});

it('updates only metadata and escapes it in the editor', function () {
    $this->post(route('admin.media.store'), ['image' => UploadedFile::fake()->image('photo.png', 32, 24), 'alt' => 'Original']);
    $media = Media::query()->sole();
    $path = $media->path;
    $this->put(route('admin.media.update', $media), ['alt' => '<script>alert(1)</script>', 'title' => 'Updated',
        'path' => '../../outside.php', 'processing_status' => 'ready', 'disk' => 'other'])->assertRedirect();
    $media->refresh();
    expect($media->path)->toBe($path)->and($media->processing_status)->toBe('queued')->and($media->disk)->toBe('public');
    $this->get(route('admin.media.show', $media))->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;', false);
});

it('tracks content revision and setting references and only archives unused images', function () {
    $this->post(route('admin.media.store'), ['image' => UploadedFile::fake()->image('photo.png', 32, 24), 'alt' => 'Test image']);
    $media = Media::query()->sole();
    app(ImageProcessor::class)->process($media->id);
    $media->refresh();
    $page = Page::query()->create(['title' => 'Test page', 'path' => '/test-page', 'blocks' => [['type' => 'image', 'media_id' => $media->id]]]);
    Post::query()->create(['title' => 'Test post', 'slug' => 'test-post', 'path' => '/test-post', 'body' => '<img src="/storage/'.dirname($media->path).'/32.webp">', 'featured_media_id' => $media->id]);
    Revision::query()->create(['revisionable_type' => Page::class, 'revisionable_id' => $page->id, 'snapshot' => ['image' => ['id' => $media->id]]]);
    Setting::query()->create(['key' => 'site.logo', 'group' => 'site', 'value' => ['logo_media_id' => $media->id]]);
    expect(app(MediaUsage::class)->references($media))->toHaveCount(4);
    $this->delete(route('admin.media.archive', $media))->assertSessionHasErrors('media');
    Page::query()->delete();
    Post::query()->delete();
    Revision::query()->delete();
    Setting::query()->delete();
    // Archived pages retain image references. Hard-delete only this synthetic fixture to test the unused branch.
    $this->delete(route('admin.media.archive', $media))->assertSessionHasErrors('media');
    Page::onlyTrashed()->forceDelete();
    $this->delete(route('admin.media.archive', $media))->assertRedirect(route('admin.media.index'));
    expect(Media::query()->count())->toBe(0);
    Storage::disk('public')->assertExists($media->path);
    $this->get(route('admin.media.preview', $media))->assertOk()->assertHeader('Content-Type', 'image/webp');
    $this->post(route('admin.media.restore', $media))->assertRedirect();
    expect(Media::query()->count())->toBe(1);
});

it('never processes or archives a legacy image', function () {
    $media = Media::query()->create(['legacy_wp_id' => 921, 'path' => 'wp-content/uploads/2023/legacy.jpg',
        'original_name' => 'legacy.jpg', 'mime' => 'image/jpeg', 'size' => 8, 'sha256' => hash('sha256', 'original'),
        'alt' => 'Original legacy alt', 'processing_status' => 'queued']);
    Storage::disk('public')->put($media->path, 'original');
    app(ImageProcessor::class)->process($media->id);
    $this->delete(route('admin.media.archive', $media))->assertForbidden();
    expect(Storage::disk('public')->get($media->path))->toBe('original');
});

it('fails safely on staging tampering and permits reupload and retry', function () {
    $bytes = UploadedFile::fake()->image('photo.png', 32, 24)->getContent();
    $this->post(route('admin.media.store'), ['image' => UploadedFile::fake()->createWithContent('photo.png', $bytes), 'alt' => 'Test image']);
    $media = Media::query()->sole();
    Storage::disk('local')->put(app(MediaPaths::class)->staging($media).'/upload.bin', 'tampered');
    app(ImageProcessor::class)->process($media->id);
    expect($media->fresh()->processing_status)->toBe('failed');
    Storage::disk('public')->assertMissing($media->path);
    $this->post(route('admin.media.store'), ['image' => UploadedFile::fake()->createWithContent('photo.png', $bytes), 'alt' => 'Ignored duplicate metadata']);
    app(ImageProcessor::class)->process($media->id);
    expect($media->fresh()->processing_status)->toBe('ready')->and(Media::query()->count())->toBe(1);
});

it('denies all media endpoints to SEO managers', function () {
    $this->post(route('admin.media.store'), ['image' => UploadedFile::fake()->image('photo.png', 32, 24), 'alt' => 'Test']);
    $media = Media::query()->sole();
    $seo = adminAccount('SEO Manager');
    $this->actingAs($seo)->withSession(verifiedAdminSession($seo));
    $this->get(route('admin.media.index'))->assertForbidden();
    $this->get(route('admin.media.create'))->assertForbidden();
    $this->post(route('admin.media.store'), ['image' => UploadedFile::fake()->image('photo.png'), 'alt' => 'Test'])->assertForbidden();
    $this->get(route('admin.media.show', $media))->assertForbidden();
    $this->get(route('admin.media.preview', $media))->assertForbidden();
    $this->get(route('admin.media.status', $media))->assertForbidden();
    $this->put(route('admin.media.update', $media), ['alt' => 'Changed'])->assertForbidden();
    $this->delete(route('admin.media.archive', $media))->assertForbidden();
});

it('does not extend an idle session through background status polling', function () {
    $this->post(route('admin.media.store'), ['image' => UploadedFile::fake()->image('photo.png', 32, 24), 'alt' => 'Test']);
    $media = Media::query()->sole();
    $timestamp = now()->timestamp - 100;
    $this->withSession(['admin.last_activity' => $timestamp])->get(route('admin.media.status', $media))->assertOk()->assertJsonPath('status', 'queued');
    expect(session('admin.last_activity'))->toBe($timestamp);
});

it('requires CSRF before accepting an upload', function () {
    app()->instance('env', 'local');
    $this->post(route('admin.media.store'), ['image' => UploadedFile::fake()->image('photo.png', 32, 24), 'alt' => 'Test'])->assertStatus(419);
    expect(Media::query()->count())->toBe(0);
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

it('accepts and decodes genuine WebP and AVIF uploads', function (string $format) {
    $image = imagecreatetruecolor(32, 24);
    ob_start();
    $format === 'avif' ? imageavif($image, null, 55) : imagewebp($image, null, 82);
    $bytes = ob_get_clean();
    imagedestroy($image);
    $this->post(route('admin.media.store'), ['image' => UploadedFile::fake()->createWithContent('image.'.$format, $bytes), 'alt' => 'Synthetic image'])->assertRedirect();
    $media = Media::query()->sole();
    app(ImageProcessor::class)->process($media->id);
    expect($media->fresh()->processing_status)->toBe('ready')->and($media->fresh()->mime)->toBe('image/png');
})->with(['webp', 'avif']);

it('applies JPEG orientation before stripping EXIF', function () {
    $jpeg = UploadedFile::fake()->image('phone.jpg', 32, 24)->getContent();
    $tiff = "II\x2A\x00".pack('V', 8).pack('v', 1).pack('vvVv', 0x0112, 3, 1, 6)."\0\0".pack('V', 0);
    $exif = "Exif\0\0".$tiff;
    $bytes = substr($jpeg, 0, 2)."\xFF\xE1".pack('n', strlen($exif) + 2).$exif.substr($jpeg, 2);
    $this->post(route('admin.media.store'), ['image' => UploadedFile::fake()->createWithContent('phone.jpg', $bytes), 'alt' => 'Synthetic rotated photo'])->assertRedirect();
    $media = Media::query()->sole();
    app(ImageProcessor::class)->process($media->id);
    $media->refresh();
    expect($media->processing_status)->toBe('ready')->and($media->width)->toBe(24)->and($media->height)->toBe(32);
    $metadata = exif_read_data(Storage::disk('public')->path($media->path), null, true);
    expect($metadata['IFD0']['Orientation'] ?? null)->toBeNull();
});
