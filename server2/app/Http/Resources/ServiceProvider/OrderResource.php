<?php

namespace App\Http\Resources\ServiceProvider;

use Carbon\Carbon;
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
            'distance' => $this->printDistance(),
            'date' => Carbon::parse($this->created_at)->diffForHumans(),
            'price' => number_format($this->subtotal, config('app.decimal_places')),
            'currency' => __('ui.currency'),
        ];
    }
}
