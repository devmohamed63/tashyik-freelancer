<?php

namespace App\Utils\Services\Daftra\DTOs;

class PaymentDTO
{
    public function __construct(
        public int $invoiceId,
        public float $amount,
        public int $clientId,
        public ?int $treasuryId = null
    ) {
    }

    public function toArray(): array
    {
        $payment = [
            'invoice_id' => $this->invoiceId,
            'client_id' => $this->clientId,
            'amount' => $this->amount,
            'date' => now()->format('Y-m-d'),
            'type' => 1, // 1 = Receipt/Income
        ];

        if ($this->treasuryId) {
            $payment['treasury_id'] = $this->treasuryId; // Bank/Treasury
        }

        return [
            'ClientPayment' => $payment,
        ];
    }
}
