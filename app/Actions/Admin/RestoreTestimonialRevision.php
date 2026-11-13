<?php

namespace App\Actions\Admin;

use App\Models\Revision;
use App\Models\Testimonial;

class RestoreTestimonialRevision
{
    public function handle(Testimonial $testimonial, Revision $revision, int $version): Testimonial
    {
        app(RestoreCatalogRevision::class)->handle($testimonial, $revision, $version);

        return $testimonial->refresh();
    }
}
