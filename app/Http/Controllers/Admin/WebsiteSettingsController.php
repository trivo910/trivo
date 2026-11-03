<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\SaveWebsiteSettings;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WebsiteSettingsRequest;
use App\Models\Media;
use App\Models\Setting;
use App\Services\Settings\WebsiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class WebsiteSettingsController extends Controller
{
    public function edit(string $section, WebsiteSettings $settings): View
    {
        Gate::authorize($settings->ability($section), Setting::class);

        return view('admin.settings.website', ['section' => $section, 'fields' => $settings->fields($section),
            'settings' => $settings->read($section), 'images' => $section === 'site'
                ? Media::query()->where('processing_status', 'ready')->whereIn('mime', ['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                    ->orderBy('id')->get(['id', 'title', 'original_name'])->mapWithKeys(fn (Media $media) => [$media->id => '#'.$media->id.' '.($media->title ?: $media->original_name)])->all() : []]);
    }

    public function update(string $section, WebsiteSettingsRequest $request, SaveWebsiteSettings $action): RedirectResponse
    {
        $action->handle($section, $request->validated());

        return redirect()->route('admin.website.edit', $section)->with('status', ucfirst($section).' settings saved.');
    }
}
