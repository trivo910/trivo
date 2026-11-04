<?php

namespace App\Actions\Admin;

use App\Models\PortfolioItem;

class SavePortfolioSeo
{
    /** @param array<string, mixed> $values */
    public function handle(PortfolioItem $portfolio, array $values): PortfolioItem
    {
        app(SaveCatalogSeo::class)->handle($portfolio, $values);

        return $portfolio->refresh();
    }
}
