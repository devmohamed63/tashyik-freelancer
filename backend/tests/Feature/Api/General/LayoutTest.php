<?php

namespace Tests\Feature\Api\General;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_layout_is_displayed(): void
    {
        $response = $this->getJson(route('api.general.layout'));

        $response->assertStatus(200);

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('logo')
                ->has('description')
                ->has('social_links')
                ->has('mobile_app_links')
                ->has('user')
                ->has('contact_info')
                ->etc()
        );
    }
}
