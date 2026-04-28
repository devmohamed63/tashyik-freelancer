<?php

namespace Tests\Feature\Console;

use App\Mail\DaftraInvoicePdfMail;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DaftraResendInvoicePdfCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_with_force_sends_daftra_link_mail_after_clearing_sent_flag(): void
    {
        Mail::fake();

        config([
            'services.daftra.subdomain' => 'testcompany',
            'services.daftra.api_key' => 'test-api-key',
            'services.daftra.invoice_pdf_enabled' => true,
            'services.daftra.invoice_pdf_bcc' => 'bcc-resend@example.com',
            'services.daftra.fetch_public_invoice_url' => false,
            'services.daftra.attach_invoice_pdf_to_email' => false,
        ]);

        $sp = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'email' => 'sp-resend-force@example.com',
        ]);
        $invoice = Invoice::factory()->create([
            'service_provider_id' => $sp->id,
            'type' => Invoice::RENEW_SUBSCRIPTION_TYPE,
            'action' => Invoice::DEBIT_ACTION,
            'amount' => 100,
            'daftra_id' => 88,
            'daftra_invoice_pdf_sent_at' => now(),
        ]);

        $this->artisan('daftra:resend-invoice-pdf', [
            'invoice_id' => (string) $invoice->id,
            '--force' => true,
        ])->assertSuccessful();

        Mail::assertSent(DaftraInvoicePdfMail::class, fn (DaftraInvoicePdfMail $m) => $m->invoice->is($invoice));

        $this->assertNotNull($invoice->fresh()->daftra_invoice_pdf_sent_at);
    }

    public function test_command_fails_when_pdf_already_sent_without_force(): void
    {
        config(['services.daftra.invoice_pdf_enabled' => true]);

        $sp = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'email' => 'sp-resend-test@example.com',
        ]);
        $invoice = Invoice::factory()->create([
            'service_provider_id' => $sp->id,
            'daftra_id' => 10,
            'type' => Invoice::RENEW_SUBSCRIPTION_TYPE,
            'daftra_invoice_pdf_sent_at' => now(),
        ]);

        $this->artisan('daftra:resend-invoice-pdf', ['invoice_id' => (string) $invoice->id])
            ->assertFailed();
    }

    public function test_command_fails_for_unknown_invoice(): void
    {
        config(['services.daftra.invoice_pdf_enabled' => true]);

        $this->artisan('daftra:resend-invoice-pdf', ['invoice_id' => '9999999'])
            ->assertFailed();
    }
}
