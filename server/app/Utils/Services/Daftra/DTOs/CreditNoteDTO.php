<?php

namespace App\Utils\Services\Daftra\DTOs;

class CreditNoteDTO
{
    private array $items = [];

    public function __construct(
        public int $clientId,
        public string $currencyCode = 'SAR',
        public ?string $notes = null,
        public ?int $discount = null,
        public int $discountType = 2, // 1 = percentage, 2 = fixed amount
        public float $taxRate = 15,
        public ?int $costCenterId = null,
        public ?int $returnAccountId = null
    ) {
    }

    public function addItem(string $item, float $unitPrice, int $quantity = 1, string $description = ''): self
    {
        $entry = [
            'item' => $item,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'description' => $description,
        ];

        // Route return to the specified account (e.g. مردود مبيعات تشييك)
        if ($this->returnAccountId) {
            $entry['income_account_id'] = $this->returnAccountId;
        }

        $this->items[] = $entry;

        return $this;
    }

    public function toArray(): array
    {
        $creditNote = [
            'client_id' => $this->clientId,
            'date' => now()->format('Y-m-d'),
            'currency_code' => $this->currencyCode,
            'notes' => $this->notes ?? '',
        ];

        if ($this->costCenterId) {
            $creditNote['cost_center_id'] = $this->costCenterId;
        }

        if ($this->taxRate > 0) {
            $creditNote['tax_rate'] = $this->taxRate;
        }

        if ($this->discount && $this->discount > 0) {
            $creditNote['discount'] = $this->discount;
            $creditNote['discount_type'] = $this->discountType;
        }

        return [
            'CreditNote' => $creditNote,
            'InvoiceItem' => $this->items,
        ];
    }
}
