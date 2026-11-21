<?php

namespace App\Services\Media;

use App\Models\Media;
use App\Models\Setting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\DB;

class MediaUsage
{
    /** @return list<array{type: string, id: int}> */
    public function references(Media $media): array
    {
        $result = [];
        $tables = [
            'authors' => ['avatar_id', 'og_image_id', 'twitter_image_id'],
            'pages' => ['og_image_id', 'twitter_image_id'],
            'posts' => ['featured_media_id', 'og_image_id', 'twitter_image_id'],
            'categories' => ['og_image_id', 'twitter_image_id'], 'tags' => ['og_image_id', 'twitter_image_id'],
            'services' => ['media_id', 'og_image_id', 'twitter_image_id'],
            'portfolio_items' => ['media_id', 'og_image_id', 'twitter_image_id'],
            'testimonials' => ['media_id'], 'faqs' => ['media_id'], 'clients' => ['media_id'],
        ];
        foreach ($tables as $table => $columns) {
            foreach (DB::table($table)->where(function ($query) use ($columns, $media) {
                foreach ($columns as $column) {
                    $query->orWhere($column, $media->id);
                }
            })->pluck('id') as $id) {
                $result[$table.':'.$id] = ['type' => $table, 'id' => (int) $id];
            }
        }
        $content = ['pages' => ['blocks'], 'posts' => ['body', 'excerpt'], 'authors' => ['bio'],
            'services' => ['body', 'blocks', 'details'], 'portfolio_items' => ['body', 'blocks', 'details'],
            'testimonials' => ['body', 'blocks', 'details'], 'faqs' => ['body', 'blocks', 'details'],
            'clients' => ['body', 'blocks', 'details'], 'revisions' => ['snapshot'], 'menu_items' => ['url']];
        foreach ($content as $table => $columns) {
            foreach (DB::table($table)->select(array_merge(['id'], $columns))->orderBy('id')->lazy(50) as $row) {
                foreach ($columns as $column) {
                    $value = $row->{$column};
                    $decoded = is_string($value) ? json_decode($value, true) : null;
                    if ($this->contains($decoded ?? $value, $media)) {
                        $result[$table.':'.$row->id] = ['type' => $table, 'id' => (int) $row->id];
                    }
                }
            }
        }
        foreach (Setting::query()->where('group', '!=', 'mail')->lazy(50) as $setting) {
            try {
                $value = $setting->value;
            } catch (DecryptException) {
                // Usage is unknown: keep archival blocked without crashing the upload redirect.
                $result['settings:'.$setting->id] = ['type' => 'unreadable_settings', 'id' => $setting->id];

                continue;
            }
            if ($this->contains($value, $media)) {
                $result['settings:'.$setting->id] = ['type' => 'settings', 'id' => $setting->id];
            }
        }

        return array_values($result);
    }

    private function contains(mixed $value, Media $media, string $key = ''): bool
    {
        if (is_array($value)) {
            foreach ($value as $name => $child) {
                $context = is_string($name) && ! is_numeric($name) ? $name : $key;
                if (in_array($key, ['image', 'media', 'logo', 'avatar'], true) && $name === 'id') {
                    $context = 'media_id';
                }
                if ($this->contains($child, $media, $context)) {
                    return true;
                }
            }
        } elseif ((is_int($value) || is_string($value)) && preg_match('/(?:^|_)(?:media|image|avatar)_?id$/i', $key)
            && (string) $value === (string) $media->id) {
            return true;
        } elseif (is_string($value) && str_contains(str_replace('\\/', '/', $value), dirname($media->path).'/')) {
            return true;
        }

        return false;
    }
}
