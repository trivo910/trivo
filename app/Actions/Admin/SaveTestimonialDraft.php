<?php

namespace App\Actions\Admin;

use App\Models\Testimonial;

class SaveTestimonialDraft
{
    /** @param array<string, mixed> $values */
    public function handle(array $values, ?Testimonial $testimonial = null): Testimonial
    {
        $testimonial ??= new Testimonial(['status' => 'draft']);
        app(SaveCatalogDraft::class)->handle($testimonial, $values);

        return $testimonial->refresh();
    }
}
