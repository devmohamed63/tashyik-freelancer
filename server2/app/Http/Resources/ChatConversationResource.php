<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'status' => $this->status,
            'lead' => [
                'name' => $this->lead_name,
                'phone' => $this->lead_phone,
                'email' => $this->lead_email,
                'registration_completed' => (bool) $this->registration_completed,
            ],
            'last_message_at' => $this->last_message_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
