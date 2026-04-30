<?php

namespace App\Utils\Services\Daftra\DTOs;

class InvoiceDTO
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
        public ?int $revenueAccountId = null
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

        // Route revenue to the specified income account (e.g. إيراد مبيعات تشييك)
        if ($this->revenueAccountId) {
            $entry['income_account_id'] = $this->revenueAccountId;
        }

        $this->items[] = $entry;

        return $this;
    }

    public function toArray(): array
    {
        $invoice = [
            'client_id' => $this->clientId,
            'date' => now()->format('Y-m-d'),
            'currency_code' => $this->currencyCode,
            'notes' => $this->notes ?? '',
        ];

        if ($this->costCenterId) {
            $invoice['cost_center_id'] = $this->costCenterId;
        }

        if ($this->taxRate > 0) {
            $invoice['tax_rate'] = $this->taxRate;
        }

        if ($this->discount && $this->discount > 0) {
            $invoice['discount'] = $this->discount;
            $invoice['discount_type'] = $this->discountType;
        }

        return [
            'Invoice' => $invoice,
            'InvoiceItem' => $this->items,
        ];
    }
}
