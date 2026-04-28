<?php

namespace Tests\Feature\Mail;

use App\Models\Invoice;
use App\Models\User;
use App\Support\ServiceProviderInvoiceMailer;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ServiceProviderInvoiceMailTest extends TestCase
{
    public function test_mailer_queues_invoice_email_to_service_provider(): void
    {
        Mail::fake();

        $sp = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'email' => 'tech-invoice-test@example.com',
        ]);

        $invoice = Invoice::factory()->create([
            'service_provider_id' => $sp->id,
            'type' => Invoice::COMPLETED_ORDER_TYPE,
            'action' => Invoice::CREDIT_ACTION,
            'amount' => 100.50,
            'daftra_id' => 501,
        ]);

        ServiceProviderInvoiceMailer::send($invoice);

        Mail::assertQueued(\App\Mail\ServiceProviderInvoiceMail::class, function (\App\Mail\ServiceProviderInvoiceMail $mailable) use ($invoice) {
            if (! $mailable->invoice->is($invoice)) {
                return false;
            }

            $html = $mailable->render();

            return str_contains($html, '/client/invoices/view/'.$invoice->daftra_id)
                && str_contains($html, 'عرض الفاتورة في دفترة')
                && str_contains($html, 'daftra.com/owner/invoices/view/'.$invoice->daftra_id);
        });
    }

    public function test_additional_services_invoice_mail_matches_styled_template_and_subject(): void
    {
        Mail::fake();

        app()->setLocale('ar');

        $sp = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'email' => 'extra-services-mail@example.com',
        ]);

        $invoice = Invoice::factory()->create([
            'service_provider_id' => $sp->id,
            'type' => Invoice::ADDITIONAL_SERVICES_TYPE,
            'action' => Invoice::CREDIT_ACTION,
            'amount' => 250,
            'target_id' => 999,
            'daftra_id' => null,
        ]);

        ServiceProviderInvoiceMailer::send($invoice);

        Mail::assertQueued(\App\Mail\ServiceProviderInvoiceMail::class, function (\App\Mail\ServiceProviderInvoiceMail $mailable) use ($invoice) {
            if (! $mailable->invoice->is($invoice)) {
                return false;
            }

            $subject = $mailable->envelope()->subject;
            $html = $mailable->render();

            return $subject === 'تم تسجيل رصيد خدمات إضافية — Tashyik'
                && str_contains($html, 'تم تسجيل رصيد خدمات إضافية')
                && str_contains($html, 'خدمات إضافية')
                && str_contains($html, '#999')
                && str_contains($html, 'فتح الفاتورة (منصة Tashyik)');
        });
    }

    public function test_mailer_skips_invalid_email(): void
    {
        Mail::fake();

        $sp = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'email' => null,
        ]);

        $invoice = Invoice::factory()->create([
            'service_provider_id' => $sp->id,
            'type' => Invoice::BANK_TRANSFER_TYPE,
            'action' => Invoice::DEBIT_ACTION,
            'amount' => 50,
        ]);

        ServiceProviderInvoiceMailer::send($invoice);

        Mail::assertNothingSent();
    }
}
