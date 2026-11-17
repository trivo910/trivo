<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;

class LeadMail extends Mailable
{
    public function __construct(public Lead $lead, public bool $thankYou) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: $this->thankYou ? [] : [new Address($this->lead->email, $this->lead->name)],
            subject: $this->thankYou ? 'Thank you for contacting Trivo' : 'New website enquiry #'.$this->lead->id,
        );
    }

    public function content(): Content
    {
        return new Content(view: $this->thankYou ? 'mail.lead-thank-you' : 'mail.lead-notification');
    }
}
