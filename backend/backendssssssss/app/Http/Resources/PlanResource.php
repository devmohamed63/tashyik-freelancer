<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
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
            'name' => $this->name,
            'duration' => $this->duration_in_months,
            'price' => $this->price == 0 ? "0" : number_format($this->price, config('app.decimal_places')),
            'currency' => __('ui.currency'),
        ];
    }
}
