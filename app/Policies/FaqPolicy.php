<?php

namespace App\Policies;

use App\Models\Faq;
use App\Models\User;

class FaqPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->can('faqs.view');
    }

    public function view(User $user, Faq $faq): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->can('faqs.create');
    }

    public function updateSeo(User $user, Faq $faq): bool
    {
        return $user->is_active && $user->can('faqs.seo') && ! $faq->trashed();
    }

    public function update(User $user, Faq $faq): bool
    {
        return $user->is_active && $user->can('faqs.update') && ! $faq->trashed() && $faq->status === 'draft' && $faq->published_at === null;
    }

    public function delete(User $user, Faq $faq): bool
    {
        return $this->update($user, $faq) && $user->can('faqs.delete') && $faq->legacy_wp_id === null && $faq->publication_locked_at === null;
    }

    public function restore(User $user, Faq $faq): bool
    {
        return $user->is_active && $user->can('faqs.delete') && $faq->trashed() && $faq->status === 'draft'
            && $faq->published_at === null && $faq->legacy_wp_id === null && $faq->publication_locked_at === null;
    }
}
