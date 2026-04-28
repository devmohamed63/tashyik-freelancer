<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PublicInvoiceViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_unsigned_public_invoice_url_is_rejected(): void
    {
        $sp = User::factory()->create(['type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE]);
        $invoice = Invoice::factory()->create(['service_provider_id' => $sp->id]);

        $this->get('/public/invoices/'.$invoice->id)->assertForbidden();
    }

    public function test_signed_public_invoice_url_renders_invoice_page(): void
    {
        $sp = User::factory()->create(['type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE]);
        $invoice = Invoice::factory()->create([
            'service_provider_id' => $sp->id,
            'daftra_id' => 99,
        ]);

        $url = URL::temporarySignedRoute(
            'public.invoices.show',
            now()->addHour(),
            ['invoice' => $invoice->id]
        );

        $this->get($url)
            ->assertOk()
            ->assertSee('فاتورة المنصة', false)
            ->assertSee((string) $invoice->id, false);
    }

    public function test_public_invoice_short_token_url_renders_without_signature_query(): void
    {
        $sp = User::factory()->create(['type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE]);
        $invoice = Invoice::factory()->create(['service_provider_id' => $sp->id]);
        $invoice->refresh();
        $this->assertNotNull($invoice->view_token);

        $this->get('/public/i/'.$invoice->view_token)
            ->assertOk()
            ->assertSee('فاتورة المنصة', false)
            ->assertSee((string) $invoice->id, false);
    }

    public function test_public_invoice_unknown_token_returns_not_found(): void
    {
        $this->get('/public/i/'.str_repeat('x', 32))->assertNotFound();
    }
}
