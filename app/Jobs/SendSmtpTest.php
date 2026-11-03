<?php

namespace App\Jobs;

use App\Mail\SmtpTestMail;
use App\Models\MailLog;
use App\Services\Mail\SmtpConfigurator;
use App\Services\Mail\SmtpErrorSanitizer;
use App\Services\Mail\SmtpSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendSmtpTest implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 30;

    public function __construct(public int $mailLogId) {}

    public function handle(SmtpConfigurator $configurator, SmtpSettings $settings, SmtpErrorSanitizer $sanitizer): void
    {
        if (MailLog::query()->whereKey($this->mailLogId)->where('status', 'queued')->update(['status' => 'sending']) !== 1) {
            return;
        }
        $log = MailLog::query()->findOrFail($this->mailLogId);
        try {
            if (! $configurator->apply()) {
                throw new \RuntimeException('Save valid SMTP settings before sending a test.');
            }
            Mail::mailer('trivo')->to((string) $log->recipient)->send(new SmtpTestMail);
            $log->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (Throwable $exception) {
            $log->update(['status' => 'failed', 'error_code' => 'smtp_error',
                'error_message' => $sanitizer->message($exception, $settings->get())]);
            // Raw SMTP exceptions must not enter failed-job payloads or application logs.
        }
    }

    public function failed(?Throwable $exception): void
    {
        MailLog::query()->whereKey($this->mailLogId)->whereIn('status', ['queued', 'sending'])
            ->update(['status' => 'failed', 'error_code' => 'worker_failed', 'error_message' => 'The test worker did not complete. Check the worker configuration.']);
    }
}
