<?php

namespace App\Actions\Admin;

use App\Models\Page;
use App\Models\Revision;
use App\Services\Content\PageSeo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SavePageSeo
{
    /** @param array<string, mixed> $values */
    public function handle(Page $page, array $values): Page
    {
        Gate::authorize('updateSeo', $page);

        return DB::transaction(function () use ($page, $values) {
            $page = Page::query()->lockForUpdate()->findOrFail($page->id);
            Gate::authorize('updateSeo', $page);
            if ((int) ($values['content_version'] ?? 0) !== (int) $page->content_version) {
                throw ValidationException::withMessages(['content_version' => 'This page changed since you opened it. Reload before saving SEO.']);
            }
            $data = app(PageSeo::class)->validate($values);
            app(PageSeo::class)->lockImages($data);
            $page->fill($data);
            $page->content_version++;
            $page->save();
            Revision::query()->create(['revisionable_type' => Page::class, 'revisionable_id' => $page->id,
                'user_id' => auth()->id(), 'snapshot' => ['kind' => 'seo', 'seo' => $data, 'content_version' => $page->content_version]]);
            activity('content')->causedBy(auth()->user())->performedOn($page)->event('page_seo_saved')->log('page_seo_saved');

            return $page;
        });
    }
}
