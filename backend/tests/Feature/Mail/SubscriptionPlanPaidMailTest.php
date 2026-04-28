<?php

namespace Tests\Feature\Mail;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Support\SubscriptionPlanPaidMailer;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SubscriptionPlanPaidMailTest extends TestCase
{
    public function test_mailer_sends_subscription_confirmation_to_registered_email(): void
    {
        Mail::fake();

        $plan = Plan::factory()->create([
            'name' => ['ar' => 'باقة ذهبية', 'en' => 'Gold'],
        ]);

        $sp = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'email' => 'sp-subscription-mail@example.com',
        ]);

        $sub = new Subscription;
        $sub->user_id = $sp->id;
        $sub->plan_id = $plan->id;
        $sub->paid_amount = 200;
        $sub->starts_at = now()->startOfDay();
        $sub->ends_at = now()->addMonth()->startOfDay();
        $sp->subscription()->save($sub);

        $invoice = Invoice::factory()->create([
            'service_provider_id' => $sp->id,
            'type' => Invoice::RENEW_SUBSCRIPTION_TYPE,
            'action' => Invoice::DEBIT_ACTION,
            'amount' => 230.00,
        ]);

        SubscriptionPlanPaidMailer::send($invoice);

        Mail::assertSent(\App\Mail\SubscriptionPlanPaidMail::class, function (\App\Mail\SubscriptionPlanPaidMail $mailable) use ($invoice) {
            return $mailable->invoice->is($invoice)
                && $mailable->isPlanRenewal === false;
        });
    }

    public function test_mailer_marks_renewal_when_prior_plan_invoice_exists(): void
    {
        Mail::fake();

        $plan = Plan::factory()->create(['name' => ['ar' => 'باقة', 'en' => 'Plan']]);

        $sp = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'email' => 'sp-renewal-mail@example.com',
        ]);

        $sub = new Subscription;
        $sub->user_id = $sp->id;
        $sub->plan_id = $plan->id;
        $sub->paid_amount = 100;
        $sub->starts_at = now()->startOfDay();
        $sub->ends_at = now()->addMonth()->startOfDay();
        $sp->subscription()->save($sub);

        Invoice::factory()->create([
            'service_provider_id' => $sp->id,
            'type' => Invoice::RENEW_SUBSCRIPTION_TYPE,
            'action' => Invoice::DEBIT_ACTION,
            'amount' => 100,
        ]);

        $secondInvoice = Invoice::factory()->create([
            'service_provider_id' => $sp->id,
            'type' => Invoice::RENEW_SUBSCRIPTION_TYPE,
            'action' => Invoice::DEBIT_ACTION,
            'amount' => 100,
        ]);

        SubscriptionPlanPaidMailer::send($secondInvoice);

        Mail::assertSent(\App\Mail\SubscriptionPlanPaidMail::class, function (\App\Mail\SubscriptionPlanPaidMail $mailable) use ($secondInvoice) {
            return $mailable->invoice->is($secondInvoice)
                && $mailable->isPlanRenewal === true;
        });
    }

    public function test_mailer_ignores_non_subscription_invoices(): void
    {
        Mail::fake();

        $sp = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'email' => 'sp-order@example.com',
        ]);

        $invoice = Invoice::factory()->create([
            'service_provider_id' => $sp->id,
            'type' => Invoice::COMPLETED_ORDER_TYPE,
            'action' => Invoice::CREDIT_ACTION,
            'amount' => 50,
        ]);

        SubscriptionPlanPaidMailer::send($invoice);

        Mail::assertNothingOutgoing();
    }

    public function test_mailer_skips_invalid_email(): void
    {
        Mail::fake();

        $sp = User::factory()->create([
            'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            'email' => 'not-an-email',
        ]);

        $invoice = Invoice::factory()->create([
            'service_provider_id' => $sp->id,
            'type' => Invoice::RENEW_SUBSCRIPTION_TYPE,
            'action' => Invoice::DEBIT_ACTION,
            'amount' => 100,
        ]);

        SubscriptionPlanPaidMailer::send($invoice);

        Mail::assertNothingOutgoing();
    }
}
