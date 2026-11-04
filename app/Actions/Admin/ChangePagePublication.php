<?php

namespace App\Actions\Admin;

use App\Models\Page;

class ChangePagePublication
{
    /** @param array<string, mixed> $values */
    public function handle(Page $page, array $values): void
    {
        app(ChangeContentPublication::class)->handle($page, $values);
    }
}
