<?php

namespace Tests\Feature\Api\General;

use App\Models\Service;
use Database\Seeders\ServiceCollectionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ServiceCollectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Service::factory(12)->create();

        $this->seed(ServiceCollectionSeeder::class);
    }

    public function test_service_collections_are_displayed(): void
    {
        $response = $this->getJson(route('api.general.service_collections'));

        $response->assertStatus(200);

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data.0.title')
                ->has('data.0.services.0.name')
                ->etc()
        );
    }
}
