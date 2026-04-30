<?php

namespace App\Http\Resources;

use App\Utils\HtmlToPlainText;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => HtmlToPlainText::convert($this->excerpt),
            'body' => $this->when($request->routeIs('api.articles.show'), $this->body),
            'featured_image' => $this->getImageUrl('card'),
            'featured_image_lg' => $this->when($request->routeIs('api.articles.show'), $this->getImageUrl('lg')),
            'og_image' => $this->when($request->routeIs('api.articles.show'), $this->getImageUrl('og')),
            'meta_title' => $this->when($request->routeIs('api.articles.show'), $this->meta_title),
            'meta_description' => $this->when(
                $request->routeIs('api.articles.show'),
                HtmlToPlainText::convert($this->meta_description)
            ),
            'is_featured' => $this->is_featured,
            'published_at' => $this->published_at?->toISOString(),
            'published_at_formatted' => $this->published_at?->isoFormat('D MMMM YYYY'),
        ];
    }
}
