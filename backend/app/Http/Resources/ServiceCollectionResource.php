<?php

namespace App\Http\Resources;

use App\Utils\Traits\HasFakeImages;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceCollectionResource extends JsonResource
{
    use HasFakeImages;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $services = $this->services->map(function ($service) {
            $image = $service->getMedia('image')->first()?->getUrl('sm');

            return [
                'id' => $service->id,
                'name' => $service->name,
                'slug' => $service->slug,
                'badge' => $service->badge ? __('ui.badges.' . $service->badge) : null,
                'price' => $service->getPrice(),
                'image' => $this->getMediaUrl($image, id: $service->id),
                'rating' => $service->getRating(),
            ];
        });

        return [
            'title' => $this->title,
            'services' => $services,
        ];
    }
}
