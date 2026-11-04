<?php

namespace App\Actions\Admin;

use App\Models\Service;

class SaveServiceSeo
{
    /** @param array<string, mixed> $values */
    public function handle(Service $service, array $values): Service
    {
        app(SaveCatalogSeo::class)->handle($service, $values);

        return $service->refresh();
    }
}
