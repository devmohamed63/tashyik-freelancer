<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Notifies the service provider with Daftra client-view + optional Tashyik links, and optionally the invoice PDF from Daftra.
 */
class DaftraInvoicePdfMail extends Mailable
{
    use Queueable;

    public function __construct(
        public Invoice $invoice,
        public ?string $daftraPdfBinary = null,
    ) {}

    public function envelope(): Envelope
    {
        $hasPdf = $this->daftraPdfBinary !== null
            && $this->daftraPdfBinary !== ''
            && str_starts_with(ltrim($this->daftraPdfBinary, "\xEF\xBB\xBF \t\r\n"), '%PDF');

        return new Envelope(
            subject: $hasPdf
                ? 'فاتورتك من دفترة (PDF مرفق) — Tashyik #'.$this->invoice->id
                : 'رابط فاتورتك في دفترة — Tashyik #'.$this->invoice->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daftra-invoice-pdf',
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if ($this->daftraPdfBinary === null || $this->daftraPdfBinary === '') {
            return [];
        }

        $trimmed = ltrim($this->daftraPdfBinary, "\xEF\xBB\xBF \t\r\n");
        if (! str_starts_with($trimmed, '%PDF')) {
            return [];
        }

        $name = 'daftra-invoice-'.$this->invoice->daftra_id.'.pdf';
        $binary = $this->daftraPdfBinary;

        return [
            Attachment::fromData(fn () => $binary, $name)
                ->withMime('application/pdf'),
        ];
    }
}
