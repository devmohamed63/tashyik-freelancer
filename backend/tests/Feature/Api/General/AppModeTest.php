<?php

namespace Tests\Feature\Api\General;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AppModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_mode_is_displayed(): void
    {
        $response = $this->getJson(route('api.general.get_app_mode'));

        $response->assertStatus(200)
            ->assertSee(['mode' => app()->environment()]);
    }
}
