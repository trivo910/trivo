<?php

namespace App\Models\Concerns;

trait HasSeo
{
    public function initializeHasSeo(): void
    {
        $this->mergeFillable([
            'seo_title', 'seo_description', 'canonical_url', 'robots', 'og_title', 'og_description',
            'og_image_id', 'twitter_title', 'twitter_description', 'twitter_image_id', 'schema_type',
            'schema_json', 'noindex', 'nofollow', 'sitemap_include', 'sitemap_priority',
        ]);
        $this->mergeCasts([
            'schema_json' => 'array', 'noindex' => 'boolean', 'nofollow' => 'boolean',
            'sitemap_include' => 'boolean', 'sitemap_priority' => 'decimal:1',
        ]);
    }
}
