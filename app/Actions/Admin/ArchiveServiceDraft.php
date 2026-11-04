<?php

namespace App\Actions\Admin;

use App\Models\Service;

class ArchiveServiceDraft
{
    public function handle(Service $service, int $version, bool $restore = false): void
    {
        app(ArchiveCatalogDraft::class)->handle($service, $version, $restore);
    }
}
