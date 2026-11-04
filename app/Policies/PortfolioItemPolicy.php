<?php

namespace App\Policies;

use App\Models\PortfolioItem;
use App\Models\User;

class PortfolioItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->can('portfolio.view');
    }

    public function view(User $user, PortfolioItem $portfolio): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->can('portfolio.create');
    }

    public function updateSeo(User $user, PortfolioItem $portfolio): bool
    {
        return $user->is_active && $user->can('portfolio.seo') && ! $portfolio->trashed();
    }

    public function update(User $user, PortfolioItem $portfolio): bool
    {
        return $user->is_active && $user->can('portfolio.update') && ! $portfolio->trashed() && $portfolio->status === 'draft' && $portfolio->published_at === null;
    }

    public function delete(User $user, PortfolioItem $portfolio): bool
    {
        return $this->update($user, $portfolio) && $user->can('portfolio.delete') && $portfolio->legacy_wp_id === null && $portfolio->publication_locked_at === null;
    }

    public function restore(User $user, PortfolioItem $portfolio): bool
    {
        return $user->is_active && $user->can('portfolio.delete') && $portfolio->trashed() && $portfolio->status === 'draft'
            && $portfolio->published_at === null && $portfolio->legacy_wp_id === null && $portfolio->publication_locked_at === null;
    }

    public function publish(User $user, PortfolioItem $portfolio): bool
    {
        return $user->is_active && $user->can('portfolio.publish') && ! $portfolio->trashed() && in_array($portfolio->status, ['draft', 'scheduled'], true);
    }

    public function cancelPublication(User $user, PortfolioItem $portfolio): bool
    {
        return $this->publish($user, $portfolio) && $portfolio->status === 'scheduled';
    }
}
