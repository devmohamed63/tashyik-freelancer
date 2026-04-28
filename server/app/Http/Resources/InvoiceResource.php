<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = Auth::user();

        $serviceProvider = $this->serviceProvider?->id != $user->id
            ? [
                'id' => $this->serviceProvider?->id,
                'name' => $this->serviceProvider?->name,
            ]
            : [
                'id' => null,
                'name' => null,
            ];

        return [
            'id' => $this->target_id,
            'type' => $this->translated_type,
            'action' => $this->action,
            'amount' => new PriceResource([$this->amount]),
            'service_provider' => $serviceProvider,
            'date' => $this->created_at?->isoFormat(config('app.time_format')),
        ];
    }
}
