<?php

namespace App\Support;

use App\Mail\ServiceProviderInvoiceMail;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class ServiceProviderInvoiceMailer
{
    public static function send(Invoice $invoice): void
    {
        $invoice->loadMissing('serviceProvider');
        $email = $invoice->serviceProvider?->email;

        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        try {
            Mail::to($email)->queue(new ServiceProviderInvoiceMail($invoice));
        } catch (\Throwable $e) {
            Log::error('ServiceProviderInvoiceMailer: failed to queue mail', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
