<?php

namespace App\Actions\Admin;

use App\Models\Menu;
use App\Services\Content\MenuDrafts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ArchiveMenu
{
    public function handle(Menu $menu, int $version, bool $restore = false): void
    {
        $ability = $restore ? 'restore' : 'delete';
        Gate::authorize($ability, $menu);
        DB::transaction(function () use ($menu, $version, $restore, $ability) {
            $menu = Menu::withTrashed()->lockForUpdate()->findOrFail($menu->id);
            Gate::authorize($ability, $menu);
            $drafts = app(MenuDrafts::class);
            $drafts->assertVersion($menu, $version);
            $menu->increment('content_version');
            $restore ? $menu->restore() : $menu->delete();
            $drafts->record($menu, $restore ? 'menu_restored' : 'menu_archived');
        });
    }
}
