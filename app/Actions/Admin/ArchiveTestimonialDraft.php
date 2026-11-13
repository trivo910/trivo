<?php

namespace App\Actions\Admin;

use App\Models\Testimonial;

class ArchiveTestimonialDraft
{
    public function handle(Testimonial $testimonial, int $version, bool $restore = false): void
    {
        app(ArchiveCatalogDraft::class)->handle($testimonial, $version, $restore);
    }
}
