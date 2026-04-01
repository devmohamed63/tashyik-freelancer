<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CategoriesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected string $indexRoute;

    protected string $showRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::factory()->create();

        $this->indexRoute = route('api.categories.index');

        $this->showRoute = route('api.categories.show', ['category' => $category->id]);
    }

    public function test_categories_list_is_displayed()
    {
        $response = $this->getJson($this->indexRoute);

        $response->assertStatus(200)->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data.0')
                ->etc()
        );
    }

    public function test_single_category_can_displayed(): void
    {
        $response = $this->getJson($this->showRoute);

        $response->assertStatus(200)->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data.id')
                ->has('data.subcategories')
                ->etc()
        );
    }
}
