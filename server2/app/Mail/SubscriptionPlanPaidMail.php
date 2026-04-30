<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionPlanPaidMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public bool $isPlanRenewal,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->isPlanRenewal
            ? 'تم تجديد اشتراكك — Tashyik'
            : 'تم تفعيل باقتك — Tashyik';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-plan-paid',
        );
    }
}
