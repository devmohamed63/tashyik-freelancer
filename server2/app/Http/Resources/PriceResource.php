<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $price = $this[0];

        return [
            'value' => (float) $price,
            'formated' => number_format($price, config('app.decimal_places')),
            'currency' => __('ui.currency')
        ];
    }
}
