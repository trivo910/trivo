<?php

namespace App\Actions\Admin;

use App\Models\Client;

class ArchiveClientDraft
{
    public function handle(Client $client, int $version, bool $restore = false): void
    {
        app(ArchiveCatalogDraft::class)->handle($client, $version, $restore);
    }
}
