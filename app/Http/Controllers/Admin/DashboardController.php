<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\UpdateTheme;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ThemeRequest;
use App\Models\User;
use App\Services\Admin\DashboardStats;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardStats $stats): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        return view('admin.dashboard', ['stats' => $stats->forUser($user)]);
    }

    public function styleguide(): View
    {
        abort_if(app()->isProduction(), 404);

        return view('admin.styleguide');
    }

    public function activity(): View
    {
        return view('admin.activity', ['activities' => Activity::query()
            ->select(['id', 'event', 'description', 'causer_id', 'created_at'])->latest()->paginate(25)]);
    }

    public function preferences(ThemeRequest $request, UpdateTheme $action): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $action->handle($user, $request->string('theme')->toString());

        return redirect()->route($request->string('return_to')->toString(), $request->input('return_to') === 'admin.website.edit'
            ? ['section' => $request->string('return_section')->toString()] : [])->with('status', 'Theme saved.');
    }
}
