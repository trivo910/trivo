<?php

namespace App\Http\Requests\Admin;

use App\Models\Testimonial;

class TestimonialMutationRequest extends ServiceMutationRequest
{
    public function authorize(): bool
    {
        $testimonial = $this->route('testimonial');
        $ability = match ($this->route()?->getName()) {
            'admin.testimonials.restore' => 'restore', 'admin.testimonials.revision' => 'update', default => 'delete',
        };

        return $testimonial instanceof Testimonial && ($this->user()?->can($ability, $testimonial) ?? false);
    }
}
