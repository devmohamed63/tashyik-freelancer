<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SitemapsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        File::deleteDirectory(public_path('sitemaps'));

        $this->artisan('app:generate-sitemaps');
    }

    public function test_sitemaps_index_is_displayed(): void
    {
        $route = route('api.sitemaps.show', ['sitemap' => 'index.xml']);

        $response = $this->get($route);

        $response->assertStatus(200);
    }

    public function test_categories_sitemap_is_displayed(): void
    {
        $route = route('api.sitemaps.show', ['sitemap' => 'categories.xml']);

        $response = $this->get($route);

        $response->assertStatus(200);
    }

    public function test_services_sitemap_is_displayed(): void
    {
        $route = route('api.sitemaps.show', ['sitemap' => 'services.xml']);

        $response = $this->get($route);

        $response->assertStatus(200);
    }

    public function test_articles_sitemap_is_displayed(): void
    {
        $route = route('api.sitemaps.show', ['sitemap' => 'articles.xml']);

        $response = $this->get($route);

        $response->assertStatus(200);
    }
}
