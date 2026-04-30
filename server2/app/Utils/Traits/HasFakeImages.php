<?php

namespace App\Utils\Traits;

trait HasFakeImages
{
    protected function getMediaUrl(
        mixed $media,
        bool $multiple = false,
        bool $multipleWithIds = false,
        int|null $id = null
    ) {
        // Return the original media on production
        if (!app()->environment('staging')) return $media;

        // Return single fake image
        if (!$multiple) return "https://picsum.photos/600?random=$id";

        // Return multiple fake images with ids
        if ($multipleWithIds) return [
            [
                'id' => 112,
                'url' => 'https://picsum.photos/600?random=1',
            ],
            [
                'id' => 113,
                'url' => 'https://picsum.photos/600?random=2',
            ],
            [
                'id' => 114,
                'url' => 'https://picsum.photos/600?random=3',
            ],
            [
                'id' => 115,
                'url' => 'https://picsum.photos/600?random=4',
            ],
            [
                'id' => 116,
                'url' => 'https://picsum.photos/600?random=5',
            ],
        ];

        // Return multiple fake images
        return [
            'https://picsum.photos/600?random=1',
            'https://picsum.photos/600?random=2',
            'https://picsum.photos/600?random=3',
            'https://picsum.photos/600?random=4',
            'https://picsum.photos/600?random=5',
        ];
    }
}
