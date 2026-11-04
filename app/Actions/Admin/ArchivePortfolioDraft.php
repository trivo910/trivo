<?php

namespace App\Actions\Admin;

use App\Models\PortfolioItem;

class ArchivePortfolioDraft
{
    public function handle(PortfolioItem $portfolio, int $version, bool $restore = false): void
    {
        app(ArchiveCatalogDraft::class)->handle($portfolio, $version, $restore);
    }
}
