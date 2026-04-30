<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->service?->name,
            'total' => number_format($this->total, config('app.decimal_places')),
            'currency' => __('ui.currency'),
            'created_at' => $this->created_at->isoFormat(config('app.time_format')),
        ];
    }
}
