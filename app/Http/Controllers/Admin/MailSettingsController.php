<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\SaveSmtpSettings;
use App\Actions\Admin\SendTestMail;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SmtpSettingsRequest;
use App\Http\Requests\Admin\TestMailRequest;
use App\Models\MailLog;
use App\Models\Setting;
use App\Models\User;
use App\Services\Mail\SmtpSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MailSettingsController extends Controller
{
    public function index(SmtpSettings $settings): View
    {
        Gate::authorize('viewMail', Setting::class);

        return view('admin.settings.mail', ['settings' => $settings->forForm(),
            'logs' => MailLog::query()->where('purpose', 'smtp_test')->latest()->limit(10)->get()]);
    }

    public function update(SmtpSettingsRequest $request, SaveSmtpSettings $action): RedirectResponse
    {
        $action->handle($request->validated());

        return redirect()->route('admin.mail.index')->with('status', 'SMTP settings saved.');
    }

    public function test(TestMailRequest $request, SendTestMail $action): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $log = $action->handle($user, $request->string('recipient')->toString());

        return redirect()->route('admin.mail.index')->with('status', $log->status === 'queued'
            ? 'Test email queued. Refresh this page to check its status.' : 'Test attempt finished. Check the delivery log below.');
    }
}
