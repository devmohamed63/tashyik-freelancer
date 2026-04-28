<?php

namespace Tests\Feature\Jobs;

use App\Jobs\SendDaftraInvoicePdfMailJob;
use App\Mail\DaftraInvoicePdfMail;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendDaftraInvoicePdfMailJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_sends_daftra_link_mail_to_service_provider_and_marks_sent_at(): void
    {
        Mail::fake();

        config([
            'services.daftra.subdomain' => 'testcompany',
            'services.daftra.api_key' => 'test-api-key',
            'services.daftra.invoice_pdf_enabled' => true,
            'services.daftra.invoice_pdf_bcc' => 'accounting-bcc@example.com',
            'services.daftra.fetch_public_invoice_url' => false,
            'services.daftra.attach_invoice_pdf_to_email' => false,
        ]);

        $sp = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'email' => 'sp-pdf-test@example.com',
        ]);

        $invoice = Invoice::factory()->create([
            'service_provider_id' => $sp->id,
            'type' => Invoice::RENEW_SUBSCRIPTION_TYPE,
            'action' => Invoice::DEBIT_ACTION,
            'amount' => 200,
            'daftra_id' => 99,
        ]);

        (new SendDaftraInvoicePdfMailJob($invoice->id))->handle();

        Mail::assertSent(DaftraInvoicePdfMail::class, function (DaftraInvoicePdfMail $mail) use ($invoice) {
            return $mail->invoice->is($invoice);
        });

        $invoice->refresh();
        $this->assertNotNull($invoice->daftra_invoice_pdf_sent_at);
    }

    public function test_job_sends_mail_without_pdf_attachment(): void
    {
        Mail::fake();

        config([
            'services.daftra.subdomain' => 'testcompany',
            'services.daftra.api_key' => 'test-api-key',
            'services.daftra.invoice_pdf_enabled' => true,
            'services.daftra.fetch_public_invoice_url' => false,
            'services.daftra.attach_invoice_pdf_to_email' => false,
        ]);

        $sp = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'email' => 'sp-link-only@example.com',
        ]);

        $invoice = Invoice::factory()->create([
            'service_provider_id' => $sp->id,
            'type' => Invoice::RENEW_SUBSCRIPTION_TYPE,
            'action' => Invoice::DEBIT_ACTION,
            'amount' => 80,
            'daftra_id' => 55,
        ]);

        (new SendDaftraInvoicePdfMailJob($invoice->id))->handle();

        Mail::assertSent(DaftraInvoicePdfMail::class, fn (DaftraInvoicePdfMail $mail) => $mail->invoice->is($invoice));

        $invoice->refresh();
        $this->assertNotNull($invoice->daftra_invoice_pdf_sent_at);
    }

    public function test_job_still_sends_when_invoice_synced_without_pdf_url_in_api(): void
    {
        Mail::fake();

        config([
            'services.daftra.subdomain' => 'testcompany',
            'services.daftra.api_key' => 'test-api-key',
            'services.daftra.invoice_pdf_enabled' => true,
            'services.daftra.fetch_public_invoice_url' => false,
            'services.daftra.attach_invoice_pdf_to_email' => false,
        ]);

        $sp = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'email' => 'sp-no-pdf@example.com',
        ]);

        $invoice = Invoice::factory()->create([
            'service_provider_id' => $sp->id,
            'type' => Invoice::RENEW_SUBSCRIPTION_TYPE,
            'action' => Invoice::DEBIT_ACTION,
            'amount' => 50,
            'daftra_id' => 77,
        ]);

        (new SendDaftraInvoicePdfMailJob($invoice->id))->handle();

        Mail::assertSent(DaftraInvoicePdfMail::class, fn (DaftraInvoicePdfMail $m) => $m->invoice->is($invoice));
        $this->assertNotNull($invoice->fresh()->daftra_invoice_pdf_sent_at);
    }

    public function test_job_fetches_and_stores_daftra_public_view_url_before_send(): void
    {
        Mail::fake();

        config([
            'services.daftra.subdomain' => 'testcompany',
            'services.daftra.api_key' => 'test-api-key',
            'services.daftra.invoice_pdf_enabled' => true,
            'services.daftra.fetch_public_invoice_url' => true,
            'services.daftra.attach_invoice_pdf_to_email' => false,
        ]);

        $preview = 'https://testcompany.daftra.com/invoices/preview/99?hash=xyz';

        Http::fake([
            '*/api2/invoices/99.json' => Http::response([
                'Invoice' => ['id' => 99, 'invoice_html_url' => $preview],
            ], 200),
        ]);

        $sp = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'email' => 'sp-fetch-preview@example.com',
        ]);

        $invoice = Invoice::factory()->create([
            'service_provider_id' => $sp->id,
            'type' => Invoice::RENEW_SUBSCRIPTION_TYPE,
            'action' => Invoice::DEBIT_ACTION,
            'amount' => 200,
            'daftra_id' => 99,
        ]);

        (new SendDaftraInvoicePdfMailJob($invoice->id))->handle();

        $this->assertSame($preview, $invoice->fresh()->daftra_public_view_url);
        Mail::assertSent(DaftraInvoicePdfMail::class);
    }

    public function test_job_attaches_daftra_pdf_when_api_returns_pdf_url_and_download_succeeds(): void
    {
        Mail::fake();

        $pdfBody = "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n";

        config([
            'services.daftra.subdomain' => 'testcompany',
            'services.daftra.api_key' => 'test-api-key',
            'services.daftra.invoice_pdf_enabled' => true,
            'services.daftra.fetch_public_invoice_url' => false,
            'services.daftra.attach_invoice_pdf_to_email' => true,
        ]);

        Http::fake(function (Request $request) use ($pdfBody) {
            $url = $request->url();
            if (str_contains($url, '/api2/invoices/101.json')) {
                return Http::response([
                    'Invoice' => [
                        'id' => 101,
                        'invoice_pdf_url' => 'https://testcompany.daftra.com/invoices/download/101.pdf?hash=abc',
                    ],
                ], 200);
            }
            if (str_contains($url, '101.pdf')) {
                return Http::response($pdfBody, 200, [
                    'Content-Type' => 'application/pdf',
                ]);
            }

            return Http::response('not found', 404);
        });

        $sp = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'email' => 'sp-pdf-attach@example.com',
        ]);

        $invoice = Invoice::factory()->create([
            'service_provider_id' => $sp->id,
            'type' => Invoice::RENEW_SUBSCRIPTION_TYPE,
            'action' => Invoice::DEBIT_ACTION,
            'amount' => 120,
            'daftra_id' => 101,
        ]);

        (new SendDaftraInvoicePdfMailJob($invoice->id))->handle();

        Mail::assertSent(DaftraInvoicePdfMail::class, function (DaftraInvoicePdfMail $mail) use ($invoice) {
            return $mail->invoice->id === $invoice->id
                && count($mail->attachments()) === 1;
        });

        $this->assertNotNull($invoice->fresh()->daftra_invoice_pdf_sent_at);
    }
}
