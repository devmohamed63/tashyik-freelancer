<?php

namespace App\Http\Resources;

use App\Utils\HtmlToPlainText;
use App\Utils\Traits\HasFakeImages;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    use HasFakeImages;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
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

        $subcategories = $this->when(
            $this->relationLoaded('children'),
            function () {
                return $this->children->map(function ($child) {
                    $image = $child->getMedia('image')->first()?->getUrl('sm');

                    return [
                        'id' => $child->id,
                        'slug' => $child->slug,
                        'name' => $child->name,
                        'badge' => $child->badge ? __('ui.badges.' . $child->badge) : null,
                        'image' => $this->getMediaUrl($image, id: $child->id),
                        'rating' => $child->getRating(),
                    ];
                });
            }
        );

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'badge' => $this->badge ? __('ui.badges.' . $this->badge) : null,
            'description' => $this->description,
            'parent' => $this->when(
                $this->relationLoaded('parent'),
                fn() => new CategoryResource($this->parent)
            ),
            'image' => $image,
            'gallery' => $gallery,
            'rating' => $this->getRating(),
            'subcategories' => $subcategories,
            'og_image' => $this->when(
                $request->routeIs('*.categories.show'),
                fn() => $this->getMedia('image')->first()?->getUrl('og')
            ),
            'meta_title' => $this->when(
                $request->routeIs('*.categories.show'),
                $this->meta_title
            ),
            'meta_description' => $this->when(
                $request->routeIs('*.categories.show'),
                HtmlToPlainText::convert($this->meta_description)
            ),
        ];
    }
}
