<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $belongs_to_institution = app()->environment('uploading') ? true : (bool) $this->institution_id;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'type' => $this->type,
            'entity_type' => $this->entity_type,
            'belongs_to_institution' => $belongs_to_institution,
            'status' => $this->status,
            'city' => new CityResource($this?->city),
            'earnings_today' => $this->earnings_today,
            'orders_today' => $this->orders_today,
            'current_order' => $this->current_order,
            'picture' => $this->getAvatarUrl('lg'),
            'tax_registration_number' => $this->tax_registration_number,

            // Institution context
            'institution' => $this->when($this->institution_id, fn() => [
                'id' => $this->institution?->id,
                'name' => $this->institution?->name,
            ]),
            'is_institution_owner' => $this->isInstitutionOrCompany(),
        ];
    }
}
