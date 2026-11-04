<?php

namespace App\Http\Requests\Admin;

use App\Models\PortfolioItem;

class PortfolioMutationRequest extends ServiceMutationRequest
{
    public function authorize(): bool
    {
        $portfolio = $this->route('portfolio');
        $ability = match ($this->route()?->getName()) {
            'admin.portfolio.restore' => 'restore', 'admin.portfolio.revision' => 'update', default => 'delete',
        };

        return $portfolio instanceof PortfolioItem && ($this->user()?->can($ability, $portfolio) ?? false);
    }
}
