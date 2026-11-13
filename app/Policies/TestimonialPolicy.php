<?php

namespace App\Policies;

use App\Models\Testimonial;
use App\Models\User;

class TestimonialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->can('testimonials.view');
    }

    public function view(User $user, Testimonial $testimonial): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->can('testimonials.create');
    }

    public function update(User $user, Testimonial $testimonial): bool
    {
        return $user->is_active && $user->can('testimonials.update') && ! $testimonial->trashed() && $testimonial->status === 'draft' && $testimonial->published_at === null;
    }

    public function delete(User $user, Testimonial $testimonial): bool
    {
        return $this->update($user, $testimonial) && $user->can('testimonials.delete') && $testimonial->legacy_wp_id === null && $testimonial->publication_locked_at === null;
    }

    public function restore(User $user, Testimonial $testimonial): bool
    {
        return $user->is_active && $user->can('testimonials.delete') && $testimonial->trashed() && $testimonial->status === 'draft'
            && $testimonial->published_at === null && $testimonial->legacy_wp_id === null && $testimonial->publication_locked_at === null;
    }
}
