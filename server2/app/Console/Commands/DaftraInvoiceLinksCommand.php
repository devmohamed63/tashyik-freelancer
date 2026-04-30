<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Utils\Services\Daftra;
use Illuminate\Console\Command;

class DaftraInvoiceLinksCommand extends Command
{
    protected $signature = 'daftra:links
                            {--limit=40 : عدد السجلات}
                            {--pending : فواتير اشتراك لها مبلغ ولم تُزامن بعد (daftra_id فارغ)}
                            {--all : آخر فواتير بأي حالة (مزامنة أو لا)}';

    protected $description = 'يعرض روابط فتح الفواتير في واجهة دفترة (owner) + رابط قائمة الفواتير';

    public function handle(Daftra $daftra): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $this->info('قائمة كل الفواتير في دفترة:');
        $this->line($daftra->ownerInvoicesIndexUrl());
        $this->newLine();

        $query = Invoice::query()->orderByDesc('id')->limit($limit);

        if ($this->option('pending')) {
            $query->where('type', Invoice::RENEW_SUBSCRIPTION_TYPE)
                ->where('amount', '>', 0)
                ->whereNull('daftra_id');
            $this->comment('وضع: فواتير اشتراك تحتاج مزامنة (لا يوجد رابط دفترة حتى تُنجح المزامنة).');
        } elseif ($this->option('all')) {
            $this->comment('وضع: آخر فواتير (كل الأنواع).');
        } else {
            $query->whereNotNull('daftra_id');
            $this->comment('وضع: فواتير لها daftra_id فقط (افتح الرابط في المتصفح بعد تسجيل الدخول لدفترة).');
        }

        $invoices = $query->get(['id', 'type', 'amount', 'daftra_id', 'daftra_payment_id']);

        if ($invoices->isEmpty()) {
            $this->warn('لا توجد صفوف تطابق الفلتر.');
            if (! $this->option('pending') && ! $this->option('all')) {
                $this->line('جرّب: php artisan daftra:sync-pending --include-subscriptions --sync');
                $this->line('أو: php artisan daftra:links --pending');
            }

            return self::SUCCESS;
        }

        foreach ($invoices as $inv) {
            $amount = number_format((float) $inv->amount, 2);
            $type = $inv->type;
            if ($inv->daftra_id) {
                $url = $daftra->ownerInvoiceViewUrl((int) $inv->daftra_id);
                $pay = $inv->daftra_payment_id ? 'قبض: نعم' : 'قبض: لا';
                $this->line("Tashyik #{$inv->id} | {$type} | {$amount} | Daftra #{$inv->daftra_id} | {$pay}");
                $this->line("  → {$url}");
            } else {
                $this->warn("Tashyik #{$inv->id} | {$type} | {$amount} | daftra_id فارغ");
            }
        }

        $this->newLine();
        $this->comment('لو رابط «عرض» رجع 404، جرّب: php artisan daftra:invoice-json {daftra_id} --resolve');
        $this->comment('أو افتح قائمة الفواتير من الرابط أعلاه وابحث برقم Daftra أو من ملاحظات الفاتورة.');

        return self::SUCCESS;
    }
}
