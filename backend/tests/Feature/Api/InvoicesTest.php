<?php

namespace Tests\Feature\Api;

use App\Models\Invoice;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class InvoicesTest extends TestCase
{
    use RefreshDatabase;

    protected User $serviceProvider;

    protected string $indexRoute;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceProvider = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
        ]);

        $invoice = Invoice::factory()->make();

        $this->serviceProvider->invoices()->save($invoice);

        $this->indexRoute = route('api.invoices.index');
    }

    public function test_invoices_list_is_displayed_to_service_provider(): void
    {
        $response = $this->actingAs($this->serviceProvider)
            ->getJson($this->indexRoute);

        $response->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->has('data')
                    ->etc()
            );
    }
}
