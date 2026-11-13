<?php

namespace App\Actions\Admin;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\Content\MenuDrafts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ChangeMenuItem
{
    /** @param array<string, mixed> $values */
    public function handle(Menu $menu, array $values, ?MenuItem $item = null, string $operation = 'save'): MenuItem
    {
        abort_unless(in_array($operation, ['save', 'archive', 'restore'], true), 404);
        $ability = $operation === 'save' ? 'update' : 'archiveItem';
        Gate::authorize($ability, $menu);

        return DB::transaction(function () use ($menu, $values, $item, $operation, $ability) {
            $menu = Menu::query()->lockForUpdate()->findOrFail($menu->id);
            Gate::authorize($ability, $menu);
            $drafts = app(MenuDrafts::class);
            $drafts->assertVersion($menu, (int) ($values['content_version'] ?? 0));
            if ($item) {
                $item = MenuItem::withTrashed()->where('menu_id', $menu->id)->lockForUpdate()->findOrFail($item->id);
                abort_if($operation === 'restore' ? ! $item->trashed() : $item->trashed(), 404);
            } else {
                abort_unless($operation === 'save', 404);
            }
            if ($operation !== 'save') {
                abort_unless($item->legacy_wp_id === null, 403);
                if ($operation === 'archive') {
                    if (MenuItem::query()->where('menu_id', $menu->id)->where('parent_id', $item->id)->exists()) {
                        throw ValidationException::withMessages(['parent_id' => 'Move or archive active child items first.']);
                    }
                    $item->delete();
                } else {
                    $drafts->assertTree($menu, ['parent_id' => $item->parent_id], $item);
                    $item->restore();
                }
            } else {
                $data = $drafts->item($values);
                $drafts->assertTree($menu, $data, $item);
                $item ??= new MenuItem(['menu_id' => $menu->id]);
                $item->fill($data)->save();
            }
            $menu->increment('content_version');
            $drafts->record($menu, 'menu_item_'.$operation, $item);

            return $item;
        });
    }
}
