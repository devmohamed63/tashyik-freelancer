<?php

namespace Tests\Feature\Api;

use App\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class BannersTest extends TestCase
{
    use RefreshDatabase;

    protected string $indexRoute;

    protected function setUp(): void
    {
        parent::setUp();

        Banner::factory(5)->create();

        $this->indexRoute = route('api.banners.index');
    }

    public function test_banners_list_is_displayed(): void
    {
        $response = $this->getJson($this->indexRoute);

        $response->assertStatus(200);

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data')
                ->etc()
        );
    }
}
