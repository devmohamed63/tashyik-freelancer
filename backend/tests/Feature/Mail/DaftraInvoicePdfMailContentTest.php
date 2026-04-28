<?php

namespace Tests\Feature\Mail;

use App\Mail\DaftraInvoicePdfMail;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DaftraInvoicePdfMailContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_rendered_body_uses_daftra_client_view_primary_when_daftra_id_even_without_public_column(): void
    {
        config(['services.tashyik.invoice_show_url' => null]);
        config(['services.daftra.subdomain' => 'acme']);

        $sp = User::factory()->create(['type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE]);
        $invoice = Invoice::factory()->create([
            'service_provider_id' => $sp->id,
            'daftra_id' => 42,
            'type' => Invoice::RENEW_SUBSCRIPTION_TYPE,
            'daftra_public_view_url' => null,
        ]);

        $invoice->refresh();

        $mailable = new DaftraInvoicePdfMail($invoice);
        $html = $mailable->render();

        $this->assertStringContainsString('https://acme.daftra.com/client/invoices/view/42', $html);
        $this->assertStringContainsString('عرض الفاتورة في دفترة', $html);
        $this->assertStringContainsString('/public/i/'.$invoice->view_token, $html);
        $this->assertStringContainsString('نفس الفاتورة على منصة Tashyik', $html);
        $this->assertStringContainsString('acme.daftra.com/owner/invoices/view/'.$invoice->daftra_id, $html);
        $this->assertStringContainsString('لوحة دفترة (حساب الشركة)', $html);
    }

    public function test_rendered_body_omits_tashyik_public_url_when_local_public_disabled(): void
    {
        config(['services.tashyik.invoice_show_url' => null]);
        config(['services.tashyik.invoice_emails_include_local_public_link' => false]);
        config(['services.daftra.subdomain' => 'acme']);

        $sp = User::factory()->create(['type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE]);
        $invoice = Invoice::factory()->create([
            'service_provider_id' => $sp->id,
            'daftra_id' => 42,
            'type' => Invoice::RENEW_SUBSCRIPTION_TYPE,
            'daftra_public_view_url' => null,
        ]);

        $invoice->refresh();
        $html = (new DaftraInvoicePdfMail($invoice))->render();

        $this->assertStringNotContainsString('/public/i/', $html);
        $this->assertStringContainsString('https://acme.daftra.com/client/invoices/view/42', $html);
        $this->assertStringContainsString('عرض الفاتورة في دفترة', $html);
    }

    public function test_rendered_body_uses_client_view_primary_not_stored_api_preview(): void
    {
        config(['services.tashyik.invoice_show_url' => null]);
        config(['services.daftra.subdomain' => 'acme']);

        $sp = User::factory()->create(['type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE]);
        $preview = 'https://acme.daftra.com/invoices/preview/42?hash=sampletoken';
        $invoice = Invoice::factory()->create([
            'service_provider_id' => $sp->id,
            'daftra_id' => 42,
            'type' => Invoice::RENEW_SUBSCRIPTION_TYPE,
            'daftra_public_view_url' => $preview,
        ]);

        $mailable = new DaftraInvoicePdfMail($invoice);
        $html = $mailable->render();

        $this->assertStringContainsString('https://acme.daftra.com/client/invoices/view/42', $html);
        $this->assertStringContainsString('عرض الفاتورة في دفترة', $html);
        $this->assertStringContainsString('نفس الفاتورة على منصة Tashyik', $html);
        $this->assertStringNotContainsString($preview, $html);
    }
}
