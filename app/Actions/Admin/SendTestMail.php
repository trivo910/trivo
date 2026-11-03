<?php

namespace App\Actions\Admin;

use App\Jobs\SendSmtpTest;
use App\Models\MailLog;
use App\Models\Setting;
use App\Models\User;
use App\Services\Mail\SmtpSettings;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SendTestMail
{
    public function handle(User $user, string $recipient): MailLog
    {
        Gate::authorize('testMail', Setting::class);
        $settings = app(SmtpSettings::class)->get();
        if (empty($settings['host']) || empty($settings['from_email'])) {
            throw ValidationException::withMessages(['recipient' => 'Save SMTP settings before sending a test email.']);
        }
        $log = MailLog::query()->create(['purpose' => 'smtp_test', 'recipient' => $recipient,
            'requested_by' => $user->id, 'status' => 'queued']);
        if ($settings['queue_enabled'] ?? true) {
            SendSmtpTest::dispatch($log->id)->onConnection('database')->afterCommit();
        } else {
            SendSmtpTest::dispatchSync($log->id);
        }
        activity('mail')->causedBy($user)->event('smtp_test_requested')->withProperties(['mail_log_id' => $log->id])->log('smtp_test_requested');

        return $log->refresh();
    }
}
