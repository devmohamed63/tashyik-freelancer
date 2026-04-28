<?php

namespace App\Support;

use App\Mail\SubscriptionPlanPaidMail;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class SubscriptionPlanPaidMailer
{
    /**
     * Notify the service provider at their registered email after a plan
     * payment (new subscription or renewal). Sent synchronously so delivery
     * does not depend on a queue worker for this transactional message.
     */
    public static function send(Invoice $invoice): void
    {
        if ($invoice->type !== Invoice::RENEW_SUBSCRIPTION_TYPE) {
            return;
        }

        $invoice->loadMissing([
            'serviceProvider.subscription.plan',
        ]);

        $email = $invoice->serviceProvider?->email;

        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $isPlanRenewal = Invoice::query()
            ->where('service_provider_id', $invoice->service_provider_id)
            ->where('type', Invoice::RENEW_SUBSCRIPTION_TYPE)
            ->where('id', '<', $invoice->id)
            ->exists();

        try {
            Mail::to($email)->send(new SubscriptionPlanPaidMail($invoice, $isPlanRenewal));
        } catch (\Throwable $e) {
            Log::error('SubscriptionPlanPaidMailer: failed to send mail', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
