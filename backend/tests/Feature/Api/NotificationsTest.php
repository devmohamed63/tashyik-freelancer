<?php

namespace Tests\Feature\Api;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Notification $notification;

    protected string $indexRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->notification = Notification::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->indexRoute = route('api.notifications.index');
    }

    public function test_notifications_list_is_displayed(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson($this->indexRoute);

        $response->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->has('data')
                    ->etc()
            );
    }
}
