<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        File::deleteDirectory(public_path('feed'));

        $this->artisan('app:generate-product-feed');
    }

    public function test_products_feed_is_displayed(): void
    {
        $route = route('api.feed.show', ['file' => 'ar.rss']);

        $response = $this->get($route);

        $response->assertStatus(200);
    }
}
