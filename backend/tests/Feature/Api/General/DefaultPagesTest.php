<?php

namespace Tests\Feature\Api\General;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class DefaultPagesTest extends TestCase
{
    use RefreshDatabase;

    protected Page $modelToDisplay;

    protected string $showRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->modelToDisplay = Page::create([
            'name' => [
                'ar' => 'Test page',
                'en' => 'Test page',
            ],
            'body' => [
                'ar' => 'content',
                'en' => 'content',
            ],
            'tag' => 'test-page'
        ]);

        $this->showRoute = route('api.general.show_default_page', ['page' => $this->modelToDisplay->tag]);
    }

    public function test_default_pages_can_displayed(): void
    {
        $response = $this->getJson($this->showRoute);

        $response->assertStatus(200);

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data')
                ->etc()
        );
    }
}
