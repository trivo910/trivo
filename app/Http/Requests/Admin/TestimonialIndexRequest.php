<?php

namespace App\Http\Requests\Admin;

use App\Models\Testimonial;

class TestimonialIndexRequest extends PageIndexRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Testimonial::class) ?? false;
    }
}
