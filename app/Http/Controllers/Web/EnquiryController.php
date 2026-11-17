<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnquiryRequest;
use App\Jobs\SendLeadMail;
use App\Models\Lead;
use App\Models\MailLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class EnquiryController extends Controller
{
    public function store(EnquiryRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $form = $request->string('form')->toString();
            $lead = Lead::query()->create([
                ...$request->safe()->only(['name', 'email', 'phone', 'company', 'message']),
                'service' => match ($form) {
                    'audit' => 'Free marketing audit',
                    'proposal' => 'Marketing proposal',
                    default => 'Contact enquiry',
                },
                'source_url' => $form === 'contact' ? route('contact') : route('home'),
                'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
                'status' => 'new',
            ]);
            foreach (['lead_notification', 'lead_thank_you'] as $purpose) {
                $log = MailLog::query()->create(['lead_id' => $lead->id, 'purpose' => $purpose,
                    'recipient' => $purpose === 'lead_thank_you' ? $lead->email : null, 'status' => 'queued']);
                SendLeadMail::dispatch($log->id)->onConnection('database')->afterCommit();
            }
        });

        return redirect()->route('thank-you');
    }
}
