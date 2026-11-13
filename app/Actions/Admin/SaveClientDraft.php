<?php

namespace App\Actions\Admin;

use App\Models\Client;

class SaveClientDraft
{
    /** @param array<string, mixed> $values */
    public function handle(array $values, ?Client $client = null): Client
    {
        $client ??= new Client(['status' => 'draft']);
        app(SaveCatalogDraft::class)->handle($client, $values);

        return $client->refresh();
    }
}
