<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceProviderInvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine(),
        );
    }

    /**
     * Match subscription renewal email style: clear Arabic subject per invoice type.
     */
    private function subjectLine(): string
    {
        return match ($this->invoice->type) {
            Invoice::ADDITIONAL_SERVICES_TYPE => 'تم تسجيل رصيد خدمات إضافية — Tashyik',
            Invoice::COMPLETED_ORDER_TYPE => 'تم تسجيل رصيد طلب مكتمل — Tashyik',
            Invoice::BANK_TRANSFER_TYPE => 'تم تسجيل خصم تحويل بنكي — Tashyik',
            default => 'إشعار فاتورة — Tashyik #'.$this->invoice->id,
        };
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.service-provider-invoice',
        );
    }
}
