<?php

namespace App\Console\Commands;

use App\Events\OrderCompleted;
use App\Jobs\SyncInvoiceToDaftra;
use App\Listeners\CreateOrderExtraInvoice;
use App\Listeners\CreateOrderInvoice;
use App\Models\Address;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderExtra;
use App\Models\Service;
use App\Models\User;
use App\Utils\Services\Daftra;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Schema;

class DaftraDemoAllCommand extends Command
{
    protected $signature = 'daftra:demo-all {email?}';

    protected $description = 'تجربة الـ 3 مسارات (اشتراك + طلب مكتمل + خدمات إضافية) ثم مزامنة Daftra. البريد اختياري.';

    public function handle(Daftra $daftra): int
    {
        if (! Schema::hasColumn('invoices', 'event_uid')) {
            $this->error('شغّل migrations أولاً (عمود event_uid).');

            return self::FAILURE;
        }

        $email = (string) ($this->argument('email') ?: 'mohmahmoudd63@gmail.com');

        $this->info('=== 1) اشتراك باقة ===');
        $this->call('daftra:demo-subscription', ['email' => $email]);

        $sp = User::query()->where('email', $email)->first();
        if (! $sp) {
            $this->error('لم يُعثر على مقدم الخدمة بالبريد.');

            return self::FAILURE;
        }

        $customer = User::factory()->create([
            'type' => User::USER_ACCOUNT_TYPE,
            'email' => 'daftra-demo-customer-'.now()->timestamp.'@example.com',
        ]);

        $service = Service::query()->first() ?? Service::factory()->create([
            'name' => ['ar' => 'خدمة تجريبية Daftra', 'en' => 'Daftra demo service'],
        ]);
        $category = Category::query()->first();
        $address = Address::query()->first();

        $this->newLine();
        $this->info('=== 2) طلب مكتمل (فاتورة مقدم خدمة) ===');

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'service_provider_id' => $sp->id,
            'service_id' => $service->id,
            'category_id' => $category?->id,
            'address_id' => $address?->id,
            'subtotal' => 200.00,
            'tax' => 30.00,
            'tax_rate' => 15,
            'total' => 230.00,
            'coupons_total' => 0,
            'wallet_balance' => 0,
            'quantity' => 1,
            'visit_cost' => 0,
            'status' => Order::COMPLETED_STATUS,
        ]);

        app(CreateOrderInvoice::class)->handle(new OrderCompleted($order->fresh()));

        $orderInvoice = Invoice::query()
            ->where('event_uid', 'order:'.$order->id.':type:'.Invoice::COMPLETED_ORDER_TYPE)
            ->first();

        if ($orderInvoice) {
            try {
                Bus::dispatchSync(new SyncInvoiceToDaftra($orderInvoice->fresh(), bankAmount: (float) $order->total));
            } catch (\Throwable $e) {
                $this->error('مزامنة طلب مكتمل: '.$e->getMessage());
            }
            $orderInvoice->refresh();
            $this->line('Tashyik invoice #'.$orderInvoice->id.' | daftra_id='.($orderInvoice->daftra_id ?? 'null'));
            if ($orderInvoice->daftra_id) {
                $this->line($daftra->ownerInvoiceViewUrl((int) $orderInvoice->daftra_id));
            }
        } else {
            $this->warn('لم تُنشأ فاتورة طلب مكتمل (تحقق من subtotal).');
        }

        $this->newLine();
        $this->info('=== 3) خدمات إضافية مدفوعة ===');

        $order2 = Order::factory()->create([
            'customer_id' => $customer->id,
            'service_provider_id' => $sp->id,
            'service_id' => $service->id,
            'category_id' => $category?->id,
            'address_id' => $address?->id,
            'subtotal' => 0,
            'tax' => 0,
            'tax_rate' => 15,
            'total' => 0,
            'coupons_total' => 0,
            'wallet_balance' => 0,
            'quantity' => 1,
            'visit_cost' => 0,
            'status' => Order::COMPLETED_STATUS,
        ]);

        $extra = OrderExtra::factory()->create([
            'order_id' => $order2->id,
            'service_id' => $service->id,
            'status' => OrderExtra::PAID_STATUS,
            'price' => 80.00,
            'materials' => 20.00,
            'tax' => 15.00,
            'tax_rate' => 15,
            'total' => 115.00,
            'quantity' => 1,
            'wallet_balance' => 0,
        ]);

        app(CreateOrderExtraInvoice::class)->handle(new OrderCompleted($order2->fresh()));

        $extraInvoice = Invoice::query()
            ->where('event_uid', 'order_extra:'.$extra->id.':type:'.Invoice::ADDITIONAL_SERVICES_TYPE)
            ->first();

        if ($extraInvoice) {
            try {
                Bus::dispatchSync(new SyncInvoiceToDaftra($extraInvoice->fresh(), bankAmount: (float) $extra->total));
            } catch (\Throwable $e) {
                $this->error('مزامنة إضافي: '.$e->getMessage());
            }
            $extraInvoice->refresh();
            $this->line('Tashyik invoice #'.$extraInvoice->id.' | daftra_id='.($extraInvoice->daftra_id ?? 'null'));
            if ($extraInvoice->daftra_id) {
                $this->line($daftra->ownerInvoiceViewUrl((int) $extraInvoice->daftra_id));
            }
        } else {
            $this->warn('لم تُنشأ فاتورة خدمات إضافية.');
        }

        $this->newLine();
        $this->info('قائمة الفواتير في دفترة:');
        $this->line($daftra->ownerInvoicesIndexUrl());
        $this->comment('ابحث عن «The API» أو عن ملاحظات تحتوي رقم الطلب / المنصة.');

        return self::SUCCESS;
    }
}
