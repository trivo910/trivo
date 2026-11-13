<?php

namespace App\Actions\Admin;

use App\Models\Menu;
use App\Services\Content\MenuDrafts;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SaveMenu
{
    /** @param array<string, mixed> $values */
    public function handle(array $values, ?Menu $menu = null): Menu
    {
        Gate::authorize($menu ? 'update' : 'create', $menu ?? Menu::class);

        try {
            return Cache::lock('menu-location-writes', 30)->block(5, fn () => DB::transaction(function () use ($values, $menu) {
                $drafts = app(MenuDrafts::class);
                if ($menu) {
                    $menu = Menu::query()->lockForUpdate()->findOrFail($menu->id);
                    Gate::authorize('update', $menu);
                    $drafts->assertVersion($menu, (int) ($values['content_version'] ?? 0));
                }
                $data = $drafts->metadata($values, $menu);
                $menu ??= new Menu;
                $menu->fill($data);
                $menu->content_version = $menu->exists ? $menu->content_version + 1 : 1;
                $menu->save();
                $drafts->record($menu, 'menu_saved');

                return $menu;
            }));
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages(['location' => 'Another menu change is in progress. Try again shortly.']);
        }
    }
}
