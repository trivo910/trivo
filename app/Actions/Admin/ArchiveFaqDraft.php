<?php

namespace App\Actions\Admin;

use App\Models\Faq;

class ArchiveFaqDraft
{
    public function handle(Faq $faq, int $version, bool $restore = false): void
    {
        app(ArchiveCatalogDraft::class)->handle($faq, $version, $restore);
    }
}
