<?php

namespace App\Services\Content;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Revision;
use App\Support\RedirectPaths;
use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MenuDrafts
{
    public function assertVersion(Menu $menu, int $version): void
    {
        if ($menu->content_version !== $version) {
            throw ValidationException::withMessages(['content_version' => 'This menu changed. Reload before continuing.']);
        }
    }

    /** @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    public function metadata(array $values, ?Menu $menu): array
    {
        $data = Validator::make($values, ['name' => ['required', 'string', 'max:150', $this->plain()],
            'location' => ['required', 'string', 'max:64', 'regex:/^[a-z][a-z0-9_-]*$/D', Rule::unique('menus', 'location')->ignore($menu?->id)]])->validate();
        if ($menu?->legacy_wp_id !== null && $menu->location !== $data['location']) {
            throw ValidationException::withMessages(['location' => 'Keep the imported menu location.']);
        }

        return $data;
    }

    /** @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    public function item(array $values): array
    {
        $data = Validator::make($values, ['label' => ['required', 'string', 'max:150', $this->plain()],
            'url' => ['required', 'string', 'max:1000'], 'parent_id' => ['nullable', 'integer', 'min:1'],
            'sort' => ['required', 'integer', 'between:0,10000'], 'target' => ['nullable', Rule::in(['_self', '_blank'])]])->validate();
        $url = $data['url'];
        $parts = parse_url($url);
        $internal = str_starts_with($url, '/') && RedirectPaths::target($url) !== null;
        $external = filter_var($url, FILTER_VALIDATE_URL) && is_array($parts) && ($parts['scheme'] ?? '') === 'https'
            && ! isset($parts['user']) && ! isset($parts['pass']);
        $anchor = preg_match('/^#[a-zA-Z][a-zA-Z0-9_-]*$/D', $url);
        if (preg_match('/[<>"\x00-\x20\x7F\\\\]/', $url) || ! ($internal || $external || $anchor)) {
            throw ValidationException::withMessages(['url' => 'Use a safe site-relative path, HTTPS URL or #section anchor.']);
        }

        return array_replace($data, ['parent_id' => empty($data['parent_id']) ? null : (int) $data['parent_id'],
            'target' => empty($data['target']) ? null : $data['target'], 'sort' => (int) $data['sort']]);
    }

    /** @param array<string, mixed> $data */
    public function assertTree(Menu $menu, array $data, ?MenuItem $item): void
    {
        $parents = MenuItem::query()->where('menu_id', $menu->id)->pluck('parent_id', 'id')->all();
        $parents[$item ? $item->id : 0] = $data['parent_id'];
        if (count($parents) > 100) {
            throw ValidationException::withMessages(['label' => 'A menu may contain up to 100 active items.']);
        }
        foreach (array_keys($parents) as $id) {
            $seen = [];
            $cursor = $id;
            while ($cursor !== null) {
                if (isset($seen[$cursor]) || ! array_key_exists($cursor, $parents) || count($seen) >= 5) {
                    throw ValidationException::withMessages(['parent_id' => 'Choose an active parent in this menu without cycles or more than five levels.']);
                }
                $seen[$cursor] = true;
                $cursor = $parents[$cursor];
            }
        }
    }

    public function record(Menu $menu, string $event, ?MenuItem $item = null): void
    {
        Revision::query()->create(['revisionable_type' => Menu::class, 'revisionable_id' => $menu->id, 'user_id' => auth()->id(),
            'snapshot' => ['kind' => 'menu', 'name' => $menu->name, 'location' => $menu->location, 'content_version' => $menu->content_version,
                'archived' => $menu->trashed(), 'event' => $event,
                'item' => $item ? $item->only(['id', 'legacy_wp_id', 'parent_id', 'label', 'url', 'target', 'sort', 'deleted_at']) : null]]);
        activity('content')->causedBy(auth()->user())->performedOn($menu)->event($event)->log($event);
    }

    private function plain(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (is_string($value) && preg_match('/[<>\x00-\x1F\x7F]/', $value)) {
                $fail('Use plain text without HTML or control characters.');
            }
        };
    }
}
