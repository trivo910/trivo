<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SmtpTestMail extends Mailable
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Trivo SMTP test');
    }

    public function content(): Content
    {
        return new Content(text: 'emails.smtp-test');
    }
}
