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
            'badge' => $this->badge ? \App\Models\Plan::BADGES[$this->badge] ?? null : null,
            'price' => $this->price == 0 ? "0" : number_format($this->price, config('app.decimal_places')),
            'currency' => __('ui.currency'),
            'features' => $this->whenLoaded('features', function () {
                return $this->features->pluck('title');
            }),
            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(function ($cat) {
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name
                    ];
                });
            }),
        ];
    }
}
