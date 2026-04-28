<?php

namespace App\Console\Commands;

use App\Events\PlanPaid;
use App\Jobs\SyncInvoiceToDaftra;
use App\Listeners\CreateSubscriptionInvoice;
use App\Listeners\RenewServiceProviderSubscription;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\User;
use App\Utils\Services\Daftra;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Schema;

class DaftraDemoSubscriptionCommand extends Command
{
    protected $signature = 'daftra:demo-subscription {email?}';

    protected $description = 'تجربة اشتراك + فاتورة + Daftra. البريد اختياري (الافتراضي mohmahmoudd63@gmail.com). مثال: daftra:demo-subscription you@mail.com';

    public function handle(Daftra $daftra): int
    {
        $email = (string) ($this->argument('email') ?: 'mohmahmoudd63@gmail.com');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('بريد غير صالح.');

            return self::FAILURE;
        }

        if (! Schema::hasColumn('invoices', 'event_uid')) {
            $this->error('جدول الفواتير بدون عمود event_uid. شغّل: php artisan migrate');

            return self::FAILURE;
        }

        $plan = Plan::query()->where('price', '>', 0)->orderBy('id')->first()
            ?? Plan::factory()->create([
                'name' => ['ar' => 'باقة تجريبية Daftra', 'en' => 'Daftra demo plan'],
                'price' => 100,
                'duration_in_days' => 30,
                'target_group' => User::INDIVIDUAL_ENTITY_TYPE,
            ]);

        $taxRate = (float) config('app.tax_rate', 15);
        $tax = $taxRate > 0 ? round((float) $plan->price * $taxRate / 100, 2) : 0;
        $paidGross = round((float) $plan->price + $tax, 2);

        $user = User::query()->firstOrCreate(
            ['email' => $email],
            array_merge(
                User::factory()->make([
                    'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
                    'email' => $email,
                ])->toArray(),
                ['email' => $email, 'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE]
            )
        );

        if ($user->type !== User::SERVICE_PROVIDER_ACCOUNT_TYPE) {
            $user->forceFill(['type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE])->save();
        }

        $user->subscription()?->delete();

        $txn = 'demo-subscription:'.$email.':'.now()->timestamp;

        $paymobData = [
            'type' => 'plan_paid',
            'service_provider_id' => $user->id,
            'plan_id' => $plan->id,
            'paid_amount' => $paidGross,
            'wallet_balance' => 0,
            'starts_at' => Carbon::now()->startOfDay()->toDateTimeString(),
            'ends_at' => Carbon::now()->addDays((int) $plan->duration_in_days)->startOfDay()->toDateTimeString(),
            'transaction_id' => $txn,
        ];

        $event = new PlanPaid($paymobData);

        $this->info("مقدم خدمة: #{$user->id} {$user->email}");
        $this->info("باقة: #{$plan->id} — سعر {$plan->price} + ضريبة {$tax} = إجمالي مدفوع {$paidGross}");

        app(RenewServiceProviderSubscription::class)->handle($event);
        app(CreateSubscriptionInvoice::class)->handle($event);

        $invoice = Invoice::query()
            ->where('event_uid', 'plan_paid:'.$txn.':type:'.Invoice::RENEW_SUBSCRIPTION_TYPE)
            ->first();

        if (! $invoice) {
            $this->error('لم تُنشأ فاتورة اشتراك (تحقق من paid_amount واللوج).');

            return self::FAILURE;
        }

        $this->info("فاتورة Tashyik: #{$invoice->id} | المبلغ: {$invoice->amount}");

        try {
            Bus::dispatchSync(new SyncInvoiceToDaftra($invoice->fresh(), bankAmount: $paidGross));
        } catch (\Throwable $e) {
            $this->error('فشلت مزامنة Daftra: '.$e->getMessage());

            return self::FAILURE;
        }

        $invoice->refresh();

        if (! $invoice->daftra_id) {
            $this->warn('المزامنة انتهت لكن daftra_id لا يزال فارغاً — راجع DAFTRA_API_KEY والساب دومين وstorage/logs/laravel.log');
            $this->line('رابط قائمة الفواتير: '.$daftra->ownerInvoicesIndexUrl());

            return self::FAILURE;
        }

        $this->info('تم تسجيل الفاتورة في دفترة: Daftra invoice id = '.$invoice->daftra_id);
        $this->comment('رابط عرض الفاتورة في دفترة:');
        $this->line($invoice->daftraInvoiceViewUrl());
        $this->newLine();
        $this->line('رقم فاتورة المنصة (Tashyik): #'.$invoice->id);

        return self::SUCCESS;
    }
}
