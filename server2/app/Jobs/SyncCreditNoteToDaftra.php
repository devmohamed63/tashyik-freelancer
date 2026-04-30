<?php

namespace App\Jobs;

use App\Models\User;
use App\Utils\Services\Daftra;
use App\Utils\Services\Daftra\DTOs\CreditNoteDTO;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncCreditNoteToDaftra implements ShouldQueue
{
    use Queueable;

    // Reliability configs
    public $tries = 3;

    public $backoff = [30, 60, 120];

    /**
     * Data needed to create the credit note.
     * We pass raw data instead of the Order model because the order
     * gets deleted in OrderController@destroy before the job runs.
     */
    public function __construct(
        public int $customerId,
        public string $serviceName,
        public float $subtotal,
        public float $taxRate,
        public int $orderId,
        public float $couponsTotal = 0,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(Daftra $daftra): void
    {
        $costCenterId = (int) $daftra->getConfig('cost_center_id');
        $returnAccountId = (int) $daftra->getConfig('return_account_id');

        try {
            $customer = User::find($this->customerId);

            if (! $customer) {
                Log::warning("Daftra CreditNote: Customer #{$this->customerId} not found, skipping.");

                return;
            }

            // Only sync client if they already exist in Daftra
            // (no point creating a new client just for a refund)
            if (! $customer->hasDaftraId()) {
                Log::info("Daftra CreditNote: Customer #{$this->customerId} not in Daftra, skipping credit note.");

                return;
            }

            $daftraClientId = $customer->daftra_id;

            $dto = new CreditNoteDTO(
                clientId: $daftraClientId,
                notes: "إلغاء طلب #{$this->orderId}",
                costCenterId: $costCenterId,
                taxRate: $this->taxRate,
                returnAccountId: $returnAccountId ?: null,
            );

            $dto->addItem(
                item: $this->serviceName,
                unitPrice: $this->subtotal,
                quantity: 1,
            );

            // Mirror coupon discount from original invoice so the credit note
            // does not exceed the actual sale value
            if ($this->couponsTotal > 0) {
                $dto->discount = (int) round($this->couponsTotal);
                $dto->discountType = 2;
            }

            $creditNoteId = $daftra->createCreditNote($dto);

            if ($creditNoteId) {
                Log::info("Daftra: Credit Note #{$creditNoteId} created for cancelled Order #{$this->orderId}");
            }
        } catch (\Throwable $e) {
            Log::error("Daftra CreditNote Failed for Order #{$this->orderId}", [
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }
}
