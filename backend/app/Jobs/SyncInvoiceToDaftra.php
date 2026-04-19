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
     */
    public function __construct(public Invoice $localInvoice)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(Daftra $daftra): void
    {
        // Ignore if already synced (Idempotency guard)
        if ($this->localInvoice->hasDaftraId()) {
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

        // Build Invoice DTO
        $dto = new InvoiceDTO(
            clientId: $daftraClientId,
            notes: "طلب مكتمل #{$order->id}",
            costCenterId: $costCenter,
            taxRate: (float) ($order->tax_rate ?? config('app.tax_rate', 15)),
            revenueAccountId: $revenueAccount ?: null,
        );

        // Main service item: unitPrice = subtotal (net before tax)
        // Daftra will auto-calculate the tax on this net amount
        // quantity = 1 because subtotal already = (price * quantity) + visit_cost
        $dto->addItem(
            item: $order->service?->getTranslation('name', 'ar') ?? 'طلب خدمة',
            unitPrice: (float) $order->subtotal,
            quantity: 1
        );

        // Apply discount from coupons_total if any
        if ($order->coupons_total && $order->coupons_total > 0) {
            $dto->discount = (int) round($order->coupons_total);
            $dto->discountType = 2; // Fixed amount
        }

        // Push to Daftra
        $daftraInvoiceId = $daftra->createInvoice($dto);

        if ($daftraInvoiceId) {
            $this->localInvoice->setDaftraId($daftraInvoiceId);
            Log::info("Daftra: Invoice #{$daftraInvoiceId} created for Order #{$order->id}");

            // Record payment receipt to the bank account
            $this->recordPayment($daftra, $daftraInvoiceId, $daftraClientId, $bankAccount);
        }
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

        // Reverse-calculate net price from paid_amount (which includes tax)
        // paid_amount = net + (net * tax_rate / 100) = net * (1 + tax_rate/100)
        // Therefore: net = paid_amount / (1 + tax_rate/100)
        $grossAmount = (float) $this->localInvoice->amount;
        $netPrice = round($grossAmount / (1 + $taxRate / 100), 2);

        $dto = new InvoiceDTO(
            clientId: $daftraClientId,
            notes: "تجديد اشتراك - مقدم خدمة #{$serviceProvider->id}",
            costCenterId: $costCenter,
            taxRate: $taxRate,
            revenueAccountId: $revenueAccount ?: null,
        );

        // Try to get plan name from the latest subscription
        $planName = $serviceProvider->subscription?->plan?->getTranslation('name', 'ar') ?? 'اشتراك';

        $dto->addItem(
            item: $planName,
            unitPrice: $netPrice,
        );

        $daftraInvoiceId = $daftra->createInvoice($dto);

        if ($daftraInvoiceId) {
            $this->localInvoice->setDaftraId($daftraInvoiceId);
            Log::info("Daftra: Invoice #{$daftraInvoiceId} created for SP #{$serviceProvider->id} subscription");

            // Record payment receipt to the bank account
            $this->recordPayment($daftra, $daftraInvoiceId, $daftraClientId, $bankAccount);
        }
    }

    /**
     * Record a payment receipt in Daftra and route to the bank account.
     */
    private function recordPayment(Daftra $daftra, int $daftraInvoiceId, int $daftraClientId, int $bankAccount): void
    {
        if (!$bankAccount) {
            return;
        }

        $paymentDTO = new PaymentDTO(
            invoiceId: $daftraInvoiceId,
            amount: (float) $this->localInvoice->amount,
            clientId: $daftraClientId,
            treasuryId: $bankAccount
        );

        $daftra->createPayment($paymentDTO);
    }
}
