<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePlatformWebUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_web_url_is_short_token_route_when_no_template(): void
    {
        config(['services.tashyik.invoice_show_url' => null]);

        $sp = User::factory()->create(['type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE]);
        $invoice = Invoice::factory()->create(['service_provider_id' => $sp->id]);

        $invoice->refresh();
        $this->assertNotNull($invoice->view_token);

        $url = $invoice->platformWebUrl();

        $this->assertStringContainsString('/public/i/'.$invoice->view_token, $url);
        $this->assertStringNotContainsString('signature=', $url);
        $this->assertStringNotContainsString('expires=', $url);
    }

    public function test_platform_web_url_falls_back_to_signed_route_when_view_token_missing(): void
    {
        config(['services.tashyik.invoice_show_url' => null]);

        $sp = User::factory()->create(['type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE]);
        $invoice = Invoice::factory()->create(['service_provider_id' => $sp->id]);

        $invoice->forceFill(['view_token' => null])->saveQuietly();

        $url = $invoice->fresh()->platformWebUrl();

        $this->assertStringContainsString('/public/invoices/'.$invoice->id, $url);
        $this->assertStringContainsString('signature=', $url);
        $this->assertStringContainsString('expires=', $url);
    }

    public function test_platform_web_url_respects_custom_template_with_placeholder(): void
    {
        config(['services.tashyik.invoice_show_url' => 'https://custom.example/invoices/{id}']);

        $sp = User::factory()->create(['type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE]);
        $invoice = Invoice::factory()->create(['service_provider_id' => $sp->id]);

        $this->assertSame(
            'https://custom.example/invoices/'.$invoice->id,
            $invoice->platformWebUrl()
        );
    }
}
