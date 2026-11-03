<?php

namespace App\Services\Settings;

use App\Models\Setting;

class WebsiteSettings
{
    public const SECTIONS = ['site', 'social', 'analytics'];

    /** @return array<string, array{label: string, type: string, max: int}> */
    public function fields(string $section): array
    {
        return match ($section) {
            'site' => [
                'site_name' => ['label' => 'Site name', 'type' => 'text', 'max' => 150],
                'tagline' => ['label' => 'Tagline', 'type' => 'text', 'max' => 250],
                'legal_name' => ['label' => 'Organisation name', 'type' => 'text', 'max' => 200],
                'contact_email' => ['label' => 'Contact email', 'type' => 'email', 'max' => 254],
                'phone' => ['label' => 'Contact phone', 'type' => 'tel', 'max' => 40],
                'whatsapp' => ['label' => 'WhatsApp number', 'type' => 'tel', 'max' => 16],
                'address' => ['label' => 'Postal address', 'type' => 'textarea', 'max' => 1000],
                'footer_text' => ['label' => 'Footer text', 'type' => 'textarea', 'max' => 500],
                'logo_media_id' => ['label' => 'Logo image', 'type' => 'media', 'max' => 0],
                'favicon_media_id' => ['label' => 'Site icon image', 'type' => 'media', 'max' => 0],
            ],
            'social' => $this->socialFields(),
            'analytics' => [
                'enabled' => ['label' => 'Tracking configuration', 'type' => 'boolean', 'max' => 0],
                'ga4_id' => ['label' => 'GA4 measurement ID', 'type' => 'text', 'max' => 32],
                'gtm_id' => ['label' => 'Google Tag Manager container ID', 'type' => 'text', 'max' => 32],
                'meta_pixel_id' => ['label' => 'Meta Pixel ID', 'type' => 'text', 'max' => 20],
                'google_verification' => ['label' => 'Google site verification token', 'type' => 'text', 'max' => 200],
                'bing_verification' => ['label' => 'Bing site verification token', 'type' => 'text', 'max' => 200],
            ],
            default => [],
        };
    }

    /** @return array<string, array{label: string, type: string, max: int}> */
    private function socialFields(): array
    {
        $fields = [];
        foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn',
            'youtube' => 'YouTube', 'x' => 'X / Twitter', 'pinterest' => 'Pinterest'] as $key => $label) {
            $fields[$key] = ['label' => $label.' profile URL', 'type' => 'url', 'max' => 500];
        }

        return $fields;
    }

    /** @return array<string, mixed> */
    public function read(string $section): array
    {
        $values = Setting::query()->where('key', 'website.'.$section)->first()->value ?? [];

        return array_intersect_key($values, $this->fields($section));
    }

    public function ability(string $section, bool $update = false): string
    {
        return ($update ? 'update' : 'view').($section === 'analytics' ? 'Analytics' : 'Site');
    }
}
