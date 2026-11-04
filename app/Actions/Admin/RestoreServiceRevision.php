<?php

namespace App\Actions\Admin;

use App\Models\Revision;
use App\Models\Service;

class RestoreServiceRevision
{
    public function handle(Service $service, Revision $revision, int $version): Service
    {
        app(RestoreCatalogRevision::class)->handle($service, $revision, $version);

        return $service->refresh();
    }
}
