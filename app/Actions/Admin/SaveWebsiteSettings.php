<?php

namespace App\Actions\Admin;

use App\Models\Media;
use App\Models\Setting;
use App\Services\Settings\WebsiteSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SaveWebsiteSettings
{
    /** @param array<string, mixed> $values */
    public function handle(string $section, array $values): void
    {
        abort_unless(in_array($section, WebsiteSettings::SECTIONS, true), 404);
        $settings = app(WebsiteSettings::class);
        Gate::authorize($settings->ability($section, true), Setting::class);
        $data = [];
        foreach ($settings->fields($section) as $name => $field) {
            $value = $values[$name] ?? null;
            $data[$name] = match ($field['type']) {
                'boolean' => (bool) $value,
                'media' => $value ? (int) $value : null,
                default => is_string($value) ? trim($value) : null,
            };
        }
        DB::transaction(function () use ($section, $data) {
            // Share archive's row lock so an image cannot be archived while it is assigned.
            $ids = array_filter([$data['logo_media_id'] ?? null, $data['favicon_media_id'] ?? null]);
            $images = Media::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach (['logo_media_id', 'favicon_media_id'] as $field) {
                if (empty($data[$field])) {
                    continue;
                }
                $image = $images->get($data[$field]);
                if (! $image || $image->processing_status !== 'ready' || trim((string) $image->alt) === '' || ! in_array($image->mime, ['image/jpeg', 'image/png', 'image/webp', 'image/avif'], true)) {
                    throw ValidationException::withMessages([$field => 'Choose a ready raster image with alt text from the image library.']);
                }
                if ($field === 'favicon_media_id' && ($image->width < 48 || $image->width !== $image->height)) {
                    throw ValidationException::withMessages([$field => 'Choose a square site icon at least 48 pixels wide.']);
                }
            }
            Setting::query()->updateOrCreate(['key' => 'website.'.$section], ['group' => $section, 'value' => $data]);
            activity('settings')->causedBy(auth()->user())->event('website_'.$section.'_updated')->log('website_'.$section.'_updated');
        });
    }
}
