<?php

namespace App\Actions\Admin;

use App\Models\PortfolioItem;
use App\Models\Revision;

class RestorePortfolioRevision
{
    public function handle(PortfolioItem $portfolio, Revision $revision, int $version): PortfolioItem
    {
        app(RestoreCatalogRevision::class)->handle($portfolio, $revision, $version);

        return $portfolio->refresh();
    }
}
