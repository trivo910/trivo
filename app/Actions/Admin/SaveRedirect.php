<?php

namespace App\Actions\Admin;

use App\Models\Redirect;
use App\Services\Redirects\RedirectGraph;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SaveRedirect
{
    /** @param array<string, mixed> $values */
    public function handle(array $values, ?Redirect $redirect = null): Redirect
    {
        Gate::authorize($redirect ? 'update' : 'create', $redirect ?? Redirect::class);

        return DB::transaction(function () use ($values, $redirect): Redirect {
            $rows = Redirect::query()->where('is_regex', false)->lockForUpdate()->get()->keyBy('from_path');
            $nodes = [];
            foreach ($rows as $row) {
                if ($row->id !== $redirect?->id) {
                    $nodes[$row->from_path] = ['to_url' => $row->to_url, 'status_code' => (int) $row->status_code];
                }
            }
            $nodes[$values['from_path']] = ['to_url' => $values['to_url'] ?? null, 'status_code' => (int) $values['status_code']];
            $flat = app(RedirectGraph::class)->flatten($nodes);
            foreach ($rows as $row) {
                if ($row->id !== $redirect?->id && isset($flat[$row->from_path]) && $row->to_url !== $flat[$row->from_path]['to_url']) {
                    $row->update(['to_url' => $flat[$row->from_path]['to_url']]);
                }
            }
            $redirect ??= new Redirect;
            $redirect->fill(['from_path' => $values['from_path'], 'to_url' => $flat[$values['from_path']]['to_url'],
                'status_code' => (int) $values['status_code'], 'is_regex' => false])->save();
            activity('redirects')->causedBy(auth()->user())->performedOn($redirect)->event('redirect_saved')->log('redirect_saved');

            return $redirect;
        });
    }
}
