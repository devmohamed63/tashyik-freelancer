<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Service;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ServicesTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;

    protected Service $service;

    protected string $indexRoute;

    protected string $showRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()->create();

        $this->service = Service::factory()->create([
            'category_id' => $this->category->id
        ]);

        $this->indexRoute = route('api.services.index');

        $this->showRoute = route('api.services.show', ['service' => $this->service->id]);
    }

    public function test_services_list_is_displayed_for_order_extra()
    {
        $route = route('api.services.get_services_for_order_extra');

        $mainCategory = Category::factory()->create();

        $subCategory = Category::factory()->create([
            'category_id' => $mainCategory->id,
        ]);

        Service::factory()->create([
            'category_id' => $subCategory->id,
        ]);

        /**
         * @var User
         */
        $serviceProvider = User::factory()->create();

        $serviceProvider->categories()->attach($mainCategory->id);

        $this->actingAs($serviceProvider)
            ->getJson($route)
            ->assertStatus(200)->assertJson(
                fn(AssertableJson $json) =>
                $json->has('data.0.id')
                    ->etc()
            );
    }

    public function test_cateogey_services_are_displayed()
    {
        $this->postJson($this->indexRoute, [
            'category' => $this->category->id,
        ])->assertStatus(200)->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data.0')
                ->etc()
        );
    }

    public function test_services_search()
    {
        $this->postJson($this->indexRoute, [
            'q' => $this->service->name,
        ])->assertStatus(200)->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data.0')
                ->etc()
        );
    }

    public function test_single_service_is_displayed()
    {
        $response = $this->getJson($this->showRoute);

        $response->assertStatus(200)->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data.id')
                ->has('data.name')
                ->has('data.description')
                ->etc()
        );
    }
}
