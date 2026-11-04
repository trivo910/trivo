<?php

namespace App\Actions\Admin;

use App\Models\PortfolioItem;

class SavePortfolioDraft
{
    /** @param array<string, mixed> $values */
    public function handle(array $values, ?PortfolioItem $portfolio = null): PortfolioItem
    {
        $portfolio ??= new PortfolioItem(['status' => 'draft']);
        app(SaveCatalogDraft::class)->handle($portfolio, $values);

        return $portfolio->refresh();
    }
}
