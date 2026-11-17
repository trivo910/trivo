<?php

namespace App\Jobs;

use App\Mail\LeadMail;
use App\Models\Lead;
use App\Models\MailLog;
use App\Services\Mail\SmtpConfigurator;
use App\Services\Mail\SmtpSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendLeadMail implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 30;

    public function __construct(public int $mailLogId) {}

    public function handle(SmtpConfigurator $configurator, SmtpSettings $settings): void
    {
        if (MailLog::query()->whereKey($this->mailLogId)->where('status', 'queued')->update(['status' => 'sending']) !== 1) {
            return;
        }
        $log = MailLog::query()->findOrFail($this->mailLogId);
        try {
            if (! $configurator->apply()) {
                throw new \RuntimeException('SMTP is not configured.');
            }
            $lead = Lead::query()->findOrFail($log->lead_id);
            $thankYou = $log->purpose === 'lead_thank_you';
            $smtp = $settings->get();
            $recipient = $thankYou ? $log->recipient : (($smtp['notification_email'] ?? null) ?: ($smtp['from_email'] ?? null));
            if (! is_string($recipient) || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Recipient is not configured.');
            }
            $log->update(['recipient' => $recipient]);
            Mail::mailer('trivo')->to($recipient)->send(new LeadMail($lead, $thankYou));
            $log->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (Throwable) {
            $log->update(['status' => 'failed', 'error_code' => 'smtp_error',
                'error_message' => 'Email could not be sent. Check SMTP settings and the notification recipient. The enquiry is saved in Leads.']);
        }
    }

    public function failed(?Throwable $exception): void
    {
        MailLog::query()->whereKey($this->mailLogId)->whereIn('status', ['queued', 'sending'])
            ->update(['status' => 'failed', 'error_code' => 'worker_failed',
                'error_message' => 'The mail worker did not complete. The enquiry is saved in Leads.']);
    }
}
