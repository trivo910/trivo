<?php

namespace App\Actions\Admin;

use App\Models\Revision;
use App\Models\Service;

class RestoreServiceSeo
{
    public function handle(Service $service, Revision $revision, int $version): Service
    {
        app(RestoreCatalogSeo::class)->handle($service, $revision, $version);

        return $service->refresh();
    }
}
