<?php

namespace App\Actions\Admin;

use App\Models\Faq;

class SaveFaqDraft
{
    /** @param array<string, mixed> $values */
    public function handle(array $values, ?Faq $faq = null): Faq
    {
        $faq ??= new Faq(['status' => 'draft']);
        app(SaveCatalogDraft::class)->handle($faq, $values);

        return $faq->refresh();
    }
}
