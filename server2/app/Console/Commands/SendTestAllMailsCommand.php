<?php

namespace App\Console\Commands;

use App\Mail\AdminNewOrderMessage;
use App\Mail\ContactMessage;
use App\Mail\DaftraInvoicePdfMail;
use App\Mail\ServiceProviderInvoiceMail;
use App\Mail\SubscriptionPlanPaidMail;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestAllMailsCommand extends Command
{
    protected $signature = 'mail:test-all
                            {email? : Where to send all previews (default: MAIL_FROM_ADDRESS)}';

    protected $description = 'Send sample copies of main transactional emails to one inbox (requires working MAIL_* in .env).';

    public function handle(): int
    {
        app()->setLocale('ar');

        $email = (string) ($this->argument('email') ?: config('mail.from.address', ''));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid or missing email. Example: php artisan mail:test-all you@example.com');

            return self::FAILURE;
        }

        $this->warn('Uses real Mail::send — check MAIL_MAILER, MAIL_HOST, and spam folder.');
        $this->line("Recipient: {$email}");
        $this->newLine();

        $sp = User::query()->where('email', $email)->first();
        if (! $sp) {
            $sp = User::factory()->create([
                'email' => $email,
                'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
            ]);
        } elseif ($sp->type !== User::SERVICE_PROVIDER_ACCOUNT_TYPE) {
            $sp->forceFill(['type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE])->save();
        }

        $failures = 0;

        $send = function (string $label, \Closure $fn) use (&$failures): void {
            try {
                $fn();
                $this->components->info($label);
            } catch (\Throwable $e) {
                $failures++;
                $this->components->error("{$label} — ".$e->getMessage());
            }
        };

        $plan = Plan::query()->first() ?? Plan::factory()->create([
            'name' => ['ar' => 'باقة تجريبية للبريد', 'en' => 'Mail test plan'],
        ]);

        $sp->subscription()?->delete();
        $subscription = new Subscription;
        $subscription->user_id = $sp->id;
        $subscription->plan_id = $plan->id;
        $subscription->paid_amount = 199.00;
        $subscription->starts_at = now()->startOfDay();
        $subscription->ends_at = now()->addDays($plan->duration_in_days ?? 30)->startOfDay();
        $sp->subscription()->save($subscription);

        $send('SubscriptionPlanPaidMail (تفعيل باقة)', function () use ($sp): void {
            $invoice = Invoice::factory()->for($sp, 'serviceProvider')->create([
                'type' => Invoice::RENEW_SUBSCRIPTION_TYPE,
                'action' => Invoice::DEBIT_ACTION,
                'amount' => 199.00,
                'daftra_id' => 9001,
                'target_id' => null,
            ]);
            Mail::to($sp->email)->send(new SubscriptionPlanPaidMail($invoice, false));
        });

        $send('SubscriptionPlanPaidMail (تجديد)', function () use ($sp): void {
            $invoice = Invoice::factory()->for($sp, 'serviceProvider')->create([
                'type' => Invoice::RENEW_SUBSCRIPTION_TYPE,
                'action' => Invoice::DEBIT_ACTION,
                'amount' => 199.00,
                'daftra_id' => 9002,
                'target_id' => null,
            ]);
            Mail::to($sp->email)->send(new SubscriptionPlanPaidMail($invoice, true));
        });

        $send('ServiceProviderInvoiceMail (خدمات إضافية)', function () use ($sp): void {
            $invoice = Invoice::factory()->for($sp, 'serviceProvider')->create([
                'type' => Invoice::ADDITIONAL_SERVICES_TYPE,
                'action' => Invoice::CREDIT_ACTION,
                'amount' => 115.50,
                'target_id' => 1110,
                'daftra_id' => 9003,
            ]);
            Mail::to($sp->email)->send(new ServiceProviderInvoiceMail($invoice));
        });

        $send('ServiceProviderInvoiceMail (طلب مكتمل)', function () use ($sp): void {
            $invoice = Invoice::factory()->for($sp, 'serviceProvider')->create([
                'type' => Invoice::COMPLETED_ORDER_TYPE,
                'action' => Invoice::CREDIT_ACTION,
                'amount' => 240.00,
                'target_id' => 2200,
                'daftra_id' => 9004,
            ]);
            Mail::to($sp->email)->send(new ServiceProviderInvoiceMail($invoice));
        });

        $send('ServiceProviderInvoiceMail (تحويل بنكي)', function () use ($sp): void {
            $invoice = Invoice::factory()->for($sp, 'serviceProvider')->create([
                'type' => Invoice::BANK_TRANSFER_TYPE,
                'action' => Invoice::DEBIT_ACTION,
                'amount' => 500.00,
                'target_id' => null,
                'daftra_id' => 9005,
            ]);
            Mail::to($sp->email)->send(new ServiceProviderInvoiceMail($invoice));
        });

        $send('DaftraInvoicePdfMail (عرض عميل /client + روابط Tashyik/لوحة — بدون PDF)', function () use ($sp): void {
            $invoice = Invoice::factory()->for($sp, 'serviceProvider')->create([
                'type' => Invoice::ADDITIONAL_SERVICES_TYPE,
                'action' => Invoice::CREDIT_ACTION,
                'amount' => 88.00,
                'target_id' => 3300,
                'daftra_id' => 9006,
            ]);
            Mail::to($sp->email)->send(new DaftraInvoicePdfMail($invoice));
        });

        $send('ContactMessage (رسالة تواصل)', function () use ($email): void {
            $contact = Contact::factory()->create([
                'name' => 'اختبار البريد',
                'email' => $email,
                'subject' => 'Mail test-all sample',
                'message' => 'This is a preview from mail:test-all.',
            ]);
            Mail::to($email)->send(new ContactMessage($contact));
        });

        try {
            $order = Order::query()->with(['category', 'service', 'address', 'serviceProvider'])->first();
            if (! $order) {
                $this->components->warn('AdminNewOrderMessage skipped — no orders in the database.');
            } else {
                Mail::to($email)->send(new AdminNewOrderMessage($order));
                $this->components->info('AdminNewOrderMessage (طلب جديد للإدارة)');
            }
        } catch (\Throwable $e) {
            $failures++;
            $this->components->error('AdminNewOrderMessage — '.$e->getMessage());
        }

        $this->newLine();
        if ($failures > 0) {
            $this->error("Finished with {$failures} failure(s).");

            return self::FAILURE;
        }

        $this->info('All previews sent successfully.');

        return self::SUCCESS;
    }
}
