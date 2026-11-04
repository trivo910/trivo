<?php

namespace App\Actions\Admin;

use App\Models\PortfolioItem;

class ChangePortfolioPublication
{
    /** @param array<string, mixed> $values */
    public function handle(PortfolioItem $portfolio, array $values): void
    {
        app(ChangeContentPublication::class)->handle($portfolio, $values);
    }
}
