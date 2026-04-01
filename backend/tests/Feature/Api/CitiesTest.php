<?php

namespace Tests\Feature\Api;

use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CitiesTest extends TestCase
{
    use RefreshDatabase;

    protected string $indexRoute;

    protected function setUp(): void
    {
        parent::setUp();

        City::factory(5)->create();

        $this->indexRoute = route('api.cities.index');
    }

    public function test_cities_list_is_displayed(): void
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
