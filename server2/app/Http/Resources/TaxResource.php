<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaxResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $tax = $this[0];

        $rate = config('app.tax_rate');

        return [
            'tax_rate' => $rate,
            'formated_taxes' => number_format($tax, config('app.decimal_places')),
            'currency' => __('ui.currency')
        ];
    }
}
