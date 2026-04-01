<?php

namespace App\Http\Resources;

use App\Utils\Traits\HasFakeImages;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ServiceResource extends JsonResource
{
    use HasFakeImages;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $highlights = $this->when(
            $this->relationLoaded('highlights'),
            fn() => HighlightResource::collection($this->highlights)
        );

        $image = $this->when($this->relationLoaded('media'), function () {
            $imageUrl = $this->getMedia('image')
                ->first()
                ?->getUrl('sm');

            return $this->getMediaUrl($imageUrl, id: $this->id);
        });

        $gallery = $this->when($this->relationLoaded('media'), function () {
            $galleryImages = $this->getMedia('gallery')
                ->map(fn($image) => $image->getUrl('sm'));

            return $this->getMediaUrl($galleryImages, multiple: true);
        });

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'available_in_user_city' => $this->available_in_user_city,
            'warranty_duration' => $this->warranty_duration,
            'highlights' => $highlights,
            'price' => $this->getPrice(),
            'image' => $image,
            'gallery' => $gallery,
            'rating' => $this->getRating(),
        ];
    }
}
