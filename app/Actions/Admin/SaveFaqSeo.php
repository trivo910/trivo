<?php

namespace App\Actions\Admin;

use App\Models\Faq;
use App\Models\Revision;
use App\Services\Content\ContentSeo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SaveFaqSeo
{
    /** @param array<string, mixed> $values */
    public function handle(Faq $faq, array $values): Faq
    {
        Gate::authorize('updateSeo', $faq);
        return DB::transaction(function () use ($faq, $values) {
            $faq = Faq::query()->lockForUpdate()->findOrFail($faq->id);
            Gate::authorize('updateSeo', $faq);
            if ((int) ($values['content_version'] ?? 0) !== (int) $faq->content_version) {
                throw ValidationException::withMessages(['content_version' => 'This FAQ changed since you opened it. Reload before saving SEO.']);
            }
            $data = app(ContentSeo::class)->validate($values, 'faq');
            app(ContentSeo::class)->lockImages($data);
            $faq->fill($data);
            $faq->content_version++;
            $faq->save();
            Revision::query()->create(['revisionable_type' => Faq::class, 'revisionable_id' => $faq->id, 'user_id' => auth()->id(), 'snapshot' => ['kind' => 'seo', 'seo' => $data, 'content_version' => $faq->content_version]]);
            activity('content')->causedBy(auth()->user())->performedOn($faq)->event('faq_seo_saved')->log('faq_seo_saved');
            return $faq;
        });
    }
}
