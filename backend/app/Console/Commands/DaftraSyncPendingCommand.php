<?php

namespace App\Console\Commands;

use App\Jobs\SyncInvoiceToDaftra;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderExtra;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Schema;

class DaftraSyncPendingCommand extends Command
{
    protected $signature = 'daftra:sync-pending
                            {--dry-run : اعرض الفواتير فقط دون إرسال لـ Daftra}
                            {--sync : نفّذ الـ job مباشرة بدون queue (يعمل من غير queue:work)}
                            {--include-subscriptions : شمل فواتير renew-subscription (مبلغ البنك = قيمة الفاتورة؛ قد يكون خاطئاً لو كان الدفع من المحفظة)}
                            {--include-additional-services : شمل فواتير additional-services}
                            {--limit=500 : أقصى عدد سجلات}';

    protected $description = 'يدفع فواتير (طلب مكتمل / اختياري: تجديد اشتراك / اختياري: خدمات إضافية) الناقصة في Daftra عبر نفس job الإنتاج';

    public function handle(): int
    {
        if (! Schema::hasColumn('invoices', 'daftra_id') || ! Schema::hasColumn('invoices', 'daftra_payment_id')) {
            $this->error('جدول الفواتير بدون أعمدة daftra: شغّل migrations (مثلاً: php artisan migrate).');

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');
        $useSync = (bool) $this->option('sync');
        $includeSubscriptions = (bool) $this->option('include-subscriptions');
        $includeAdditionalServices = (bool) $this->option('include-additional-services');
        $limit = max(1, (int) $this->option('limit'));

        $types = [Invoice::COMPLETED_ORDER_TYPE];
        if ($includeSubscriptions) {
            $types[] = Invoice::RENEW_SUBSCRIPTION_TYPE;
        }
        if ($includeAdditionalServices) {
            $types[] = Invoice::ADDITIONAL_SERVICES_TYPE;
        }

        $query = Invoice::query()
            ->whereIn('type', $types)
            // فواتير اشتراك بمبلغ 0 لا تُنشأ في Daftra — لا تعيق الفواتير الحقيقية
            ->where(function ($q): void {
                $q->whereNot('type', Invoice::RENEW_SUBSCRIPTION_TYPE)
                    ->orWhere('amount', '>', 0);
            })
            ->where(function ($q): void {
                $q->whereNull('daftra_id')
                    ->orWhere(function ($q2): void {
                        $q2->whereNotNull('daftra_id')
                            ->whereNull('daftra_payment_id');
                    });
            })
            ->orderBy('id')
            ->limit($limit);

        $invoices = $query->get();
        if ($invoices->isEmpty()) {
            $this->info('لا توجد فواتير ناقصة تطابق الشروط.');

            return self::SUCCESS;
        }

        $this->info("عدد السجلات: {$invoices->count()}");

        foreach ($invoices as $invoice) {
            $bankAmount = $this->resolveBankAmount($invoice, $includeSubscriptions, $includeAdditionalServices);
            if ($bankAmount === null) {
                $this->warn("تخطي #{$invoice->id} ({$invoice->type}): غير ممكن تحديد مبلغ التحصيل.");

                continue;
            }

            if ($invoice->type === Invoice::RENEW_SUBSCRIPTION_TYPE && (float) $invoice->amount <= 0) {
                $this->warn("تخطي #{$invoice->id} (renew-subscription): مبلغ الفاتورة صفر — لن تُنشأ في Daftra.");

                continue;
            }

            $row = "invoice #{$invoice->id} | {$invoice->type} | bankAmount={$bankAmount}";
            if ($dry) {
                $this->line("[dry-run] {$row}");

                continue;
            }

            $fresh = $invoice->fresh();
            if (! $fresh) {
                continue;
            }

            try {
                if ($useSync) {
                    Bus::dispatchSync(new SyncInvoiceToDaftra($fresh, bankAmount: $bankAmount));
                    $after = $invoice->fresh();
                    if ($after && in_array($after->type, [
                        Invoice::RENEW_SUBSCRIPTION_TYPE,
                        Invoice::COMPLETED_ORDER_TYPE,
                        Invoice::ADDITIONAL_SERVICES_TYPE,
                    ], true) && ! $after->hasDaftraId()) {
                        $this->warn("لم يُحفَظ daftra_id لـ #{$invoice->id} — راجع المبلغ وملف اللوج (Daftra API).");
                    } else {
                        $this->line("OK {$row} (sync)");
                    }
                } else {
                    SyncInvoiceToDaftra::dispatch($fresh, bankAmount: $bankAmount);
                    $this->line("OK {$row} (queued)");
                }
            } catch (\Throwable $e) {
                $this->error("فشل #{$invoice->id}: {$e->getMessage()}");
            }
        }

        if (! $dry && ! $useSync) {
            $this->newLine();
            $this->comment('لتنفيذ الـ jobs شغّل: php artisan queue:work');
        }

        if ($includeSubscriptions) {
            $this->newLine();
            $this->warn('للاشترا renew-subscription: إن دفع من المحفظة فقط، مبلغ البنك في Daftra قد يحتاج مراجعة يدوية (البيانات غير مخزّنة في الفاتورة).');
        }

        return self::SUCCESS;
    }

    private function resolveBankAmount(Invoice $invoice, bool $includeSubscriptions, bool $includeAdditionalServices): ?float
    {
        if ($invoice->type === Invoice::COMPLETED_ORDER_TYPE) {
            $order = Order::query()->find($invoice->target_id);
            if (! $order) {
                return null;
            }

            return (float) ($order->total ?? 0);
        }

        if ($invoice->type === Invoice::RENEW_SUBSCRIPTION_TYPE) {
            if (! $includeSubscriptions) {
                return null;
            }

            // غير مُخزَّن: نفضّل إرسال مبلغ الفاتورة كتقدير (مدفوع كامل لبوابة الدفع) عند --include-subscriptions
            return max(0, (float) $invoice->amount);
        }

        if ($invoice->type === Invoice::ADDITIONAL_SERVICES_TYPE) {
            if (! $includeAdditionalServices) {
                return null;
            }

            $eventUid = (string) ($invoice->event_uid ?? '');
            if (preg_match('/^order_extra:(\d+):type:additional-services$/', $eventUid, $matches) !== 1) {
                $this->warn("legacy additional-services invoice #{$invoice->id}: skipped (missing order_extra reference in event_uid)");

                return null;
            }

            $orderExtra = OrderExtra::query()->find((int) $matches[1]);
            if (! $orderExtra) {
                return null;
            }

            return (float) ($orderExtra->total ?? 0);
        }

        return null;
    }
}
