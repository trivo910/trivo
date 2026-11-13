<?php

namespace App\Actions\Admin;

use App\Models\Faq;
use App\Models\Revision;

class RestoreFaqRevision
{
    public function handle(Faq $faq, Revision $revision, int $version): Faq
    {
        app(RestoreCatalogRevision::class)->handle($faq, $revision, $version);

        return $faq->refresh();
    }
}
