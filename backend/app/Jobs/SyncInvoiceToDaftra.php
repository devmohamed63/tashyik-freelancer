<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\User;
use App\Utils\Services\Daftra;
use App\Utils\Services\Daftra\DTOs\InvoiceDTO;
use App\Utils\Services\Daftra\DTOs\PaymentDTO;
use Illuminate\Support\Facades\Log;

class SyncInvoiceToDaftra implements ShouldQueue
{
    use Queueable;

    // Reliability configs
    public $tries = 3;
    public $backoff = [30, 60, 120]; // Exponential backoff

    /**
     * Create a new job instance.
     *
     * @param  Invoice  $localInvoice  The local invoice to sync.
     * @param  float    $bankAmount    Actual amount that reached the bank
     *                                 (order.total, or paid_amount - wallet for
     *                                 subscriptions). Used to record the
     *                                 Daftra payment receipt correctly so that
     *                                 the bank balance in Daftra matches
     *                                 reality even with coupons or wallet use.
     */
    public function __construct(
        public Invoice $localInvoice,
        public float $bankAmount = 0,
    ) {
    }

    /**
     * Execute the job.
     *
     * Two-phase idempotency:
     *  - daftra_id is set after the invoice is created in Daftra.
     *  - daftra_payment_id is set after the payment receipt is recorded.
     * If both are set we skip entirely. If only the invoice is set (payment
     * previously failed), we skip invoice creation but retry the payment.
     */
    public function handle(Daftra $daftra): void
    {
        // Fully synced already — nothing to do
        if ($this->localInvoice->hasDaftraId() && $this->localInvoice->daftra_payment_id) {
            return;
        }

        // Only sync relevant invoice types
        $syncableTypes = [
            Invoice::COMPLETED_ORDER_TYPE,
            Invoice::RENEW_SUBSCRIPTION_TYPE,
        ];

        if (!in_array($this->localInvoice->type, $syncableTypes)) {
            return;
        }

        $costCenterId = (int) $daftra->getConfig('cost_center_id');
        $bankAccountId = (int) $daftra->getConfig('bank_account_id');
        $revenueAccountId = (int) $daftra->getConfig('revenue_account_id');

        try {
            if ($this->localInvoice->type === Invoice::COMPLETED_ORDER_TYPE) {
                $this->syncOrderInvoice($daftra, $costCenterId, $bankAccountId, $revenueAccountId);
            } elseif ($this->localInvoice->type === Invoice::RENEW_SUBSCRIPTION_TYPE) {
                $this->syncSubscriptionInvoice($daftra, $costCenterId, $bankAccountId, $revenueAccountId);
            }
        } catch (\Throwable $e) {
            Log::error("Daftra Sync Failed for Invoice #{$this->localInvoice->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Sync a completed order invoice to Daftra.
     *
     * Financial breakdown in Semiona:
     * - subtotal = service_price * quantity + visit_cost (صافي قبل الضريبة)
     * - tax = subtotal * tax_rate (ضريبة القيمة المضافة)
     * - coupons_total = discount amount from coupon codes (مقدار الخصم)
     * - total = subtotal + tax - coupons_total - wallet_balance (المبلغ المطلوب من بوابة الدفع)
     *
     * What we send to Daftra:
     * - item unit_price = subtotal (net before tax, already includes price*qty+visit)
     * - quantity = 1 (subtotal is already the total net amount)
     * - tax_rate = order.tax_rate (Daftra calculates tax automatically on the net)
     * - discount = coupons_total (fixed amount off)
     * - payment amount = localInvoice.amount = subtotal + tax (ما يُضاف لرصيد مقدم الخدمة)
     */
    private function syncOrderInvoice(Daftra $daftra, int $costCenter, int $bankAccount, int $revenueAccount): void
    {
        $order = Order::with(['service', 'customer'])->find($this->localInvoice->target_id);

        if (!$order) {
            Log::warning("Daftra: Order #{$this->localInvoice->target_id} not found, skipping.");
            return;
        }

        if (!$order->customer) {
            Log::warning("Daftra: Customer for Order #{$order->id} not found, skipping.");
            return;
        }

        // Sync customer first
        $daftraClientId = $daftra->syncClient($order->customer);
        if (!$daftraClientId) {
            Log::warning("Daftra: Could not sync client for User #{$order->customer->id}");
            return;
        }

        $daftraInvoiceId = $this->localInvoice->daftra_id;

        // Skip invoice creation if it was already pushed in a previous attempt
        if (!$daftraInvoiceId) {
            $dto = new InvoiceDTO(
                clientId: $daftraClientId,
                notes: "طلب مكتمل #{$order->id}",
                costCenterId: $costCenter,
                taxRate: (float) ($order->tax_rate ?? config('app.tax_rate', 15)),
                revenueAccountId: $revenueAccount ?: null,
            );

            // unitPrice = subtotal (net before tax); Daftra applies tax on top.
            // quantity=1 because subtotal already = (price*qty)+visit_cost
            $dto->addItem(
                item: $order->service?->getTranslation('name', 'ar') ?? 'طلب خدمة',
                unitPrice: (float) $order->subtotal,
                quantity: 1
            );

            if ($order->coupons_total && $order->coupons_total > 0) {
                $dto->discount = (int) round($order->coupons_total);
                $dto->discountType = 2;
            }

            $daftraInvoiceId = $daftra->createInvoice($dto);

            if (!$daftraInvoiceId) {
                return;
            }

            $this->localInvoice->setDaftraId($daftraInvoiceId);
            Log::info("Daftra: Invoice #{$daftraInvoiceId} created for Order #{$order->id}");
        }

        // Record payment receipt to the bank account. If this failed in a
        // previous attempt, daftra_id is set but daftra_payment_id is not,
        // and we retry ONLY this step.
        $this->recordPayment($daftra, $daftraInvoiceId, $daftraClientId, $bankAccount);
    }

    /**
     * Sync a subscription renewal invoice to Daftra.
     *
     * Financial breakdown in Semiona:
     * - PlanController: paid_amount = plan.price + tax (المبلغ شامل الضريبة)
     * - CreateSubscriptionInvoice: invoice.amount = paid_amount (شامل الضريبة)
     *
     * CRITICAL: paid_amount is INCLUSIVE OF TAX (price + 15% VAT).
     * Daftra needs the NET price, and it will add tax on top.
     * So we must reverse-calculate:
     *   net_price = paid_amount / (1 + tax_rate / 100)
     *
     * NOTE: CreateSubscriptionInvoice does NOT set target_id, and the Subscription
     * record may not exist yet when this Job runs (created in a separate listener).
     * So we use the invoice's service_provider_id to identify the client instead.
     */
    private function syncSubscriptionInvoice(Daftra $daftra, int $costCenter, int $bankAccount, int $revenueAccount): void
    {
        // Use service_provider_id from the invoice directly
        // (safer than target_id which is not set for subscription invoices)
        $serviceProvider = User::find($this->localInvoice->service_provider_id);

        if (!$serviceProvider) {
            Log::warning("Daftra: Service provider #{$this->localInvoice->service_provider_id} not found, skipping.");
            return;
        }

        $daftraClientId = $daftra->syncClient($serviceProvider);
        if (!$daftraClientId) {
            Log::warning("Daftra: Could not sync client for User #{$serviceProvider->id}");
            return;
        }

        $taxRate = (float) config('app.tax_rate', 15);

        $daftraInvoiceId = $this->localInvoice->daftra_id;

        if (!$daftraInvoiceId) {
            // Reverse-calculate net price from paid_amount (which includes tax):
            //   paid_amount = net * (1 + tax_rate/100)  →  net = paid / (1+rate)
            $grossAmount = (float) $this->localInvoice->amount;
            $netPrice = round($grossAmount / (1 + $taxRate / 100), 2);

            $dto = new InvoiceDTO(
                clientId: $daftraClientId,
                notes: "تجديد اشتراك - مقدم خدمة #{$serviceProvider->id}",
                costCenterId: $costCenter,
                taxRate: $taxRate,
                revenueAccountId: $revenueAccount ?: null,
            );

            $planName = $serviceProvider->subscription?->plan?->getTranslation('name', 'ar') ?? 'اشتراك';

            $dto->addItem(
                item: $planName,
                unitPrice: $netPrice,
            );

            $daftraInvoiceId = $daftra->createInvoice($dto);

            if (!$daftraInvoiceId) {
                return;
            }

            $this->localInvoice->setDaftraId($daftraInvoiceId);
            Log::info("Daftra: Invoice #{$daftraInvoiceId} created for SP #{$serviceProvider->id} subscription");
        }

        // Record payment receipt to the bank account
        // bankAmount = paid_amount - wallet_balance (set by CreateSubscriptionInvoice)
        $this->recordPayment($daftra, $daftraInvoiceId, $daftraClientId, $bankAccount);
    }

    /**
     * Record a payment receipt in Daftra and route it to the bank account.
     *
     * Uses `$this->bankAmount` (passed by the dispatcher) which is the actual
     * amount that hit the treasury — NOT the gross invoice amount — so that
     * coupons and wallet deductions don't inflate the Daftra bank balance.
     *
     * If bankAmount <= 0 (e.g. fully paid from wallet) we skip the receipt
     * entirely: no real money entered the bank account.
     *
     * On success the returned payment id is persisted so a retry cannot
     * create a duplicate receipt (two-phase idempotency).
     */
    private function recordPayment(Daftra $daftra, int $daftraInvoiceId, int $daftraClientId, int $bankAccount): void
    {
        if ($this->localInvoice->daftra_payment_id) {
            return; // already recorded on a previous attempt
        }

        if (!$bankAccount || $this->bankAmount <= 0) {
            return;
        }

        $paymentDTO = new PaymentDTO(
            invoiceId: $daftraInvoiceId,
            amount: (float) $this->bankAmount,
            clientId: $daftraClientId,
            treasuryId: $bankAccount
        );

        $daftraPaymentId = $daftra->createPayment($paymentDTO);

        if ($daftraPaymentId) {
            $this->localInvoice->update(['daftra_payment_id' => $daftraPaymentId]);
        }
    }
}
