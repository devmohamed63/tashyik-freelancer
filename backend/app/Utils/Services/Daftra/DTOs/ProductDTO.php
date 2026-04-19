<?php

namespace App\Utils\Services\Daftra\DTOs;

class ProductDTO
{
    public function __construct(
        public string $name,
        public float $sellingPrice,
        public string $description = '',
        public ?int $incomeAccountId = null,
        public ?int $expenseAccountId = null,
        public int $type = 2 // 1 = Product, 2 = Service
    ) {
    }

    public function toArray(): array
    {
        $product = [
            'name' => $this->name,
            'description' => $this->description,
            'selling_price' => $this->sellingPrice,
            'type' => $this->type,
        ];

        if ($this->incomeAccountId) {
            $product['income_account_id'] = $this->incomeAccountId;
        }

        if ($this->expenseAccountId) {
            $product['expense_account_id'] = $this->expenseAccountId;
        }

        return [
            'Product' => $product,
        ];
    }
}
