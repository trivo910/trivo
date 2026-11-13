<?php

namespace App\Actions\Admin;

use App\Models\Client;
use App\Models\Revision;

class RestoreClientRevision
{
    public function handle(Client $client, Revision $revision, int $version): Client
    {
        app(RestoreCatalogRevision::class)->handle($client, $revision, $version);

        return $client->refresh();
    }
}
