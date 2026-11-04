<?php

namespace App\Actions\Admin;

use App\Models\Service;

class SaveServiceDraft
{
    /** @param array<string, mixed> $values */
    public function handle(array $values, ?Service $service = null): Service
    {
        $service ??= new Service(['status' => 'draft']);
        app(SaveCatalogDraft::class)->handle($service, $values);

        return $service->refresh();
    }
}
