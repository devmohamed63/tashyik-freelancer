<?php

namespace Tests\Feature\Api;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AddressesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Address $address;

    protected string $indexRoute;

    protected string $storeRoute;

    protected string $showRoute;

    protected string $updateRoute;

    protected string $deleteRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->address = Address::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->indexRoute = route('api.addresses.index');

        $this->storeRoute = route('api.addresses.store');

        $this->showRoute = route('api.addresses.show', [
            'address' => $this->address->id
        ]);

        $this->updateRoute = route('api.addresses.update', [
            'address' => $this->address->id
        ]);

        $this->deleteRoute = route('api.addresses.destroy', [
            'address' => $this->address->id
        ]);
    }

    public function test_user_addresses_are_displayed(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson($this->indexRoute);

        $response->assertStatus(200);

        $response->assertJson(
            fn(AssertableJson $json) =>
            $json->has('data')
                ->etc()
        );
    }

    public function test_user_can_save_a_new_address(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson($this->storeRoute, [
                'name' => 'test',
                'latitude' => 52.92207,
                'longitude' => 74.43213,
                'address' => 'test',
                'landmark' => 'test',
                'building_number' => 144,
                'floor_number' => 5,
                'apartment_number' => 10,
                'is_default' => false,
            ]);

        $response->assertStatus(201);
    }

    public function test_user_can_view_address(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson($this->showRoute);

        $response->assertStatus(200)
            ->assertSee(['name' => $this->address->name]);
    }

    public function test_user_can_update_address(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson($this->updateRoute, [
                'name' => 'test',
                'latitude' => 52.92207,
                'longitude' => 74.43213,
                'address' => 'test',
                'landmark' => 'test',
                'building_number' => 144,
                'floor_number' => 5,
                'apartment_number' => 10,
                'is_default' => false,
            ]);

        $response->assertStatus(200);
    }

    public function test_user_can_delete_address(): void
    {
        $response = $this->actingAs($this->user)
            ->deleteJson($this->deleteRoute);

        $response->assertStatus(200);
    }
}
