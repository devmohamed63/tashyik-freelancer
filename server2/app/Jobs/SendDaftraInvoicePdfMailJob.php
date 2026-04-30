<?php

namespace App\Jobs;

use App\Mail\DaftraInvoicePdfMail;
use App\Models\Invoice;
use App\Utils\Services\Daftra;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends the service provider an email with links and, when possible, the Daftra invoice PDF attached.
 */
class SendDaftraInvoicePdfMailJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $invoiceId) {}

    public function uniqueId(): string
    {
        return 'daftra-invoice-pdf-'.$this->invoiceId;
    }

    public function handle(): void
    {
        if (! (bool) config('services.daftra.invoice_pdf_enabled')) {
            return;
        }

        $invoice = Invoice::query()->with('serviceProvider')->find($this->invoiceId);
        if (! $invoice || ! $invoice->daftra_id) {
            return;
        }

        if ($invoice->daftra_invoice_pdf_sent_at !== null) {
            return;
        }

        $daftra = app(Daftra::class);
        $invoice->fillDaftraPublicViewUrlFromApi($daftra);
        $invoice = $invoice->fresh(['serviceProvider']);

        $syncable = [
            Invoice::COMPLETED_ORDER_TYPE,
            Invoice::RENEW_SUBSCRIPTION_TYPE,
            Invoice::ADDITIONAL_SERVICES_TYPE,
        ];
        if (! in_array($invoice->type, $syncable, true)) {
            return;
        }

        $to = $invoice->serviceProvider?->email;
        if (! $to || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Log::warning('SendDaftraInvoicePdfMailJob: skip — invalid service provider email', [
                'invoice_id' => $invoice->id,
            ]);

            return;
        }

        $bcc = config('services.daftra.invoice_pdf_bcc');

        $pdfBinary = null;
        if ((bool) config('services.daftra.attach_invoice_pdf_to_email', true)) {
            $raw = $daftra->fetchSalesInvoicePdfBinary((int) $invoice->daftra_id);
            if (is_string($raw) && $raw !== '') {
                $trimmed = ltrim($raw, "\xEF\xBB\xBF \t\r\n");
                if (str_starts_with($trimmed, '%PDF')) {
                    $pdfBinary = $raw;
                } else {
                    Log::warning('SendDaftraInvoicePdfMailJob: Daftra PDF URL did not return a PDF body', [
                        'invoice_id' => $invoice->id,
                        'daftra_id' => $invoice->daftra_id,
                    ]);
                }
            }
        }

        try {
            $pending = Mail::to($to);
            if (is_string($bcc) && $bcc !== '' && filter_var($bcc, FILTER_VALIDATE_EMAIL)) {
                $pending->bcc($bcc);
            }

            $pending->send(new DaftraInvoicePdfMail($invoice, $pdfBinary));

            $invoice->forceFill(['daftra_invoice_pdf_sent_at' => now()])->save();
        } catch (\Throwable $e) {
            Log::error('SendDaftraInvoicePdfMailJob: send failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
