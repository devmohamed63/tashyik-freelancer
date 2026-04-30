<?php

namespace App\Utils\Services\Daftra\DTOs;

class ExpenseDTO
{
    public function __construct(
        public float $amount,
        public int $treasuryId,
        public int $journalAccountId,
        public ?string $notes = null,
        public ?string $date = null,
    ) {}

    public function toArray(): array
    {
        return [
            'Expense' => [
                'date' => $this->date ?? now()->format('Y-m-d'),
                'amount' => $this->amount,
                'treasury_id' => $this->treasuryId,
                'journal_account_id' => $this->journalAccountId,
                'details' => $this->notes ?? '',
            ],
        ];
    }
}
