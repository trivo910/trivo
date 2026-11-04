<?php

namespace App\Actions\Admin;

use App\Models\Service;

class ChangeServicePublication
{
    /** @param array<string, mixed> $values */
    public function handle(Service $service, array $values): void
    {
        app(ChangeContentPublication::class)->handle($service, $values);
    }
}
