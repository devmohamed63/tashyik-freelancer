<?php

namespace Tests\Feature\Api\General;

use App\Models\Coupon;
use App\Models\User;
use Database\Seeders\ServiceCollectionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class WelcomeCouponTest extends TestCase
{
    use RefreshDatabase;

    public User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Coupon::factory()->create([
            'welcome' => true,
        ]);

        $this->user = User::factory()->create();
    }

    public function test_welcome_coupon_is_displayed(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson(route('api.general.get_welcome_coupon'));

        $response->assertStatus(200);

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('coupon.code')
                ->has('coupon.value')
                ->etc()
        );
    }
}
