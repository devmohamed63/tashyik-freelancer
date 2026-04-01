<?php

namespace Tests\Feature\Api;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    protected Page $page;

    protected string $indexRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->page = Page::factory()->create();

        $this->indexRoute = route('api.pages.index');
    }


    public function test_pages_list_is_displayed(): void
    {
        $response = $this->getJson($this->indexRoute);

        $response->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->has('data')
                    ->etc()
            );
    }
}
