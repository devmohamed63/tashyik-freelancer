<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Support\SubscriptionPlanPaidMailer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SendTestSubscriptionPlanMailCommand extends Command
{
    protected $signature = 'mail:test-subscription-plan {email?}';

    protected $description = 'Send a sample subscription confirmation email (default: mohmahmoudd63@gmail.com; uses DB and MAIL_* config)';

    public function handle(): int
    {
        $email = (string) ($this->argument('email') ?: 'mohmahmoudd63@gmail.com');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address.');

            return self::FAILURE;
        }

        $plan = Plan::query()->first() ?? Plan::factory()->create([
            'name' => ['ar' => 'باقة تجريبية', 'en' => 'Test plan'],
        ]);

        $user = User::query()->firstOrCreate(
            ['email' => $email],
            User::factory()->make([
                'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
                'email' => $email,
            ])->toArray(),
        );

        if ($user->type !== User::SERVICE_PROVIDER_ACCOUNT_TYPE) {
            $user->forceFill(['type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE])->save();
        }

        $user->subscription()?->delete();

        $subscription = new Subscription;
        $subscription->user_id = $user->id;
        $subscription->plan_id = $plan->id;
        $subscription->paid_amount = 115.00;
        $subscription->starts_at = now()->startOfDay();
        $subscription->ends_at = now()->addDays($plan->duration_in_days ?? 30)->startOfDay();
        $user->subscription()->save($subscription);

        $invoice = new Invoice([
            'service_provider_id' => $user->id,
            'type' => Invoice::RENEW_SUBSCRIPTION_TYPE,
            'action' => Invoice::DEBIT_ACTION,
            'amount' => 115.00,
        ]);
        if (Schema::hasColumn('invoices', 'event_uid')) {
            $invoice->event_uid = 'mail_test:'.uniqid('', true);
        }
        $invoice->save();

        SubscriptionPlanPaidMailer::send($invoice);

        $this->info("Subscription confirmation sample sent to {$email}.");

        return self::SUCCESS;
    }
}
