<?php

namespace Tests\Feature\Console;

use App\Mail\ContactMessage;
use App\Mail\DaftraInvoicePdfMail;
use App\Mail\ServiceProviderInvoiceMail;
use App\Mail\SubscriptionPlanPaidMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendTestAllMailsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_test_all_dispatches_expected_mailables(): void
    {
        Mail::fake();

        $this->artisan('mail:test-all', ['email' => 'mail-test-all@example.com'])
            ->assertSuccessful();

        Mail::assertSent(SubscriptionPlanPaidMail::class, 2);
        Mail::assertQueued(ServiceProviderInvoiceMail::class, 3);
        Mail::assertSent(DaftraInvoicePdfMail::class, 1);
        Mail::assertSent(ContactMessage::class, 1);
    }
}
