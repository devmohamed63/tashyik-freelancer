<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $availableTypes = Invoice::AVAILABLE_TYPES;
        $availableActions = Invoice::AVAILABLE_ACTIONS;

        return [
            'target_id' => rand(100, 2500),
            'type' => $availableTypes[array_rand($availableTypes)],
            'action' => $availableActions[array_rand($availableActions)],
            'amount' => rand(10, 300),
        ];
    }
}
