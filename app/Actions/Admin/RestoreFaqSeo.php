<?php

namespace App\Actions\Admin;

use App\Models\Faq;
use App\Models\Revision;
use Illuminate\Support\Facades\Gate;

class RestoreFaqSeo
{
    public function handle(Faq $faq, Revision $revision, int $version): Faq
    {
        Gate::authorize('updateSeo', $faq);
        $snapshot = $revision->snapshot ?? [];
        abort_unless($revision->revisionable_type === Faq::class && (int) $revision->revisionable_id === $faq->id && ($snapshot['kind'] ?? null) === 'seo' && is_array($snapshot['seo'] ?? null), 404);
        return app(SaveFaqSeo::class)->handle($faq, array_replace($snapshot['seo'], ['content_version' => $version]));
    }
}
