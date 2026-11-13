<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ArchiveMenu;
use App\Actions\Admin\ChangeMenuItem;
use App\Actions\Admin\SaveMenu;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MenuDraftRequest;
use App\Http\Requests\Admin\MenuIndexRequest;
use App\Http\Requests\Admin\MenuItemRequest;
use App\Http\Requests\Admin\MenuMutationRequest;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Revision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(MenuIndexRequest $request): View
    {
        $query = $request->input('state') === 'archived' ? Menu::onlyTrashed() : Menu::query();
        if ($request->filled('q')) {
            $query->where(fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%')->orWhere('location', 'like', '%'.$request->string('q').'%'));
        }

        return view('admin.menus.index', ['menus' => $query->orderBy('name')->orderBy('id')->paginate(20)->withQueryString()]);
    }

    public function create(): View
    {
        Gate::authorize('create', Menu::class);

        return $this->form(new Menu(['content_version' => 1]));
    }

    public function edit(Request $request, Menu $menu): View
    {
        Gate::authorize('view', $menu);
        $request->validate(['item' => ['nullable', 'integer', 'min:1']]);
        $item = $request->filled('item') ? MenuItem::query()->where('menu_id', $menu->id)->findOrFail($request->integer('item')) : null;

        return $this->form($menu, $item);
    }

    private function form(Menu $menu, ?MenuItem $item = null): View
    {
        $items = $menu->exists ? MenuItem::query()->where('menu_id', $menu->id)->orderBy('sort')->orderBy('id')->get() : collect();

        return view('admin.menus.edit', ['menu' => $menu, 'item' => $item, 'items' => $items,
            'parents' => ['' => 'Top level'] + $items->reject(fn (MenuItem $entry) => $entry->id === $item?->id)
                ->mapWithKeys(fn (MenuItem $entry) => [$entry->id => '#'.$entry->id.' '.$entry->label])->all(),
            'archived' => $menu->exists ? MenuItem::onlyTrashed()->where('menu_id', $menu->id)->orderByDesc('id')->paginate(20, ['*'], 'archived_page') : null,
            'history' => $menu->exists ? Revision::query()->where('revisionable_type', Menu::class)->where('revisionable_id', $menu->id)
                ->where('snapshot->kind', 'menu')->latest('id')->limit(10)->get() : collect()]);
    }

    public function store(MenuDraftRequest $request, SaveMenu $action): RedirectResponse
    {
        return redirect()->route('admin.menus.edit', $action->handle($request->validated()))->with('status', 'Menu created.');
    }

    public function update(MenuDraftRequest $request, Menu $menu, SaveMenu $action): RedirectResponse
    {
        $action->handle($request->validated(), $menu);

        return redirect()->route('admin.menus.edit', $menu)->with('status', 'Menu saved.');
    }

    public function saveItem(MenuItemRequest $request, Menu $menu, ChangeMenuItem $action, ?MenuItem $item = null): RedirectResponse
    {
        $item = $action->handle($menu, $request->validated(), $item);

        return redirect()->route('admin.menus.edit', ['menu' => $menu, 'item' => $item->id])->with('status', 'Menu item saved.');
    }

    public function archiveItem(MenuItemRequest $request, Menu $menu, MenuItem $item, ChangeMenuItem $action): RedirectResponse
    {
        $action->handle($menu, $request->validated(), $item, 'archive');

        return redirect()->route('admin.menus.edit', $menu)->with('status', 'Menu item archived.');
    }

    public function restoreItem(MenuItemRequest $request, Menu $menu, MenuItem $item, ChangeMenuItem $action): RedirectResponse
    {
        $action->handle($menu, $request->validated(), $item, 'restore');

        return redirect()->route('admin.menus.edit', $menu)->with('status', 'Menu item restored.');
    }

    public function archive(MenuMutationRequest $request, Menu $menu, ArchiveMenu $action): RedirectResponse
    {
        $action->handle($menu, $request->integer('content_version'));

        return redirect()->route('admin.menus.index')->with('status', 'Menu archived.');
    }

    public function restore(MenuMutationRequest $request, Menu $menu, ArchiveMenu $action): RedirectResponse
    {
        $action->handle($menu, $request->integer('content_version'), true);

        return redirect()->route('admin.menus.edit', $menu)->with('status', 'Menu restored.');
    }
}
