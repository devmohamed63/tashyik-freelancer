<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DaftraReconcileCommand extends Command
{
    protected $signature = 'daftra:reconcile {--days=7 : عدد الأيام الأخيرة للتقرير}';

    protected $description = 'تقرير مطابقة محلي/دفترة + تتبع حوالات bank-transfer التي فشلت مزامنتها إلى Daftra';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $from = now()->subDays($days)->startOfDay();

        $summary = Invoice::query()
            ->selectRaw('type, COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN daftra_id IS NOT NULL THEN 1 ELSE 0 END) as with_daftra_id')
            ->selectRaw('SUM(CASE WHEN daftra_payment_id IS NOT NULL THEN 1 ELSE 0 END) as with_daftra_payment')
            ->where('created_at', '>=', $from)
            ->groupBy('type')
            ->orderBy('type')
            ->get();

        $this->info("Daftra reconcile - last {$days} day(s)");
        $this->newLine();

        $this->table(
            ['type', 'total', 'daftra_id', 'daftra_payment_id'],
            $summary->map(fn ($row) => [
                $row->type,
                (int) $row->total,
                (int) $row->with_daftra_id,
                (int) $row->with_daftra_payment,
            ])->toArray(),
        );

        if (Schema::hasColumn('invoices', 'event_uid')) {
            $duplicates = DB::table('invoices')
                ->select('event_uid', DB::raw('COUNT(*) as dup_count'))
                ->whereNotNull('event_uid')
                ->groupBy('event_uid')
                ->havingRaw('COUNT(*) > 1')
                ->orderByDesc('dup_count')
                ->limit(20)
                ->get();

            $this->newLine();
            $this->info('Duplicate event_uid rows: '.$duplicates->count());
            if ($duplicates->isNotEmpty()) {
                $this->table(
                    ['event_uid', 'count'],
                    $duplicates->map(fn ($row) => [$row->event_uid, (int) $row->dup_count])->toArray(),
                );
            }
        }

        if (Schema::hasColumn('invoices', 'recorded_in_daftra')) {
            $pendingTransfers = Invoice::query()
                ->where('type', Invoice::BANK_TRANSFER_TYPE)
                ->where('recorded_in_daftra', false)
                ->orderByDesc('id')
                ->limit(50)
                ->get(['id', 'service_provider_id', 'amount', 'created_at']);

            $this->newLine();
            $this->warn('Pending bank-transfer Daftra sync failures: '.$pendingTransfers->count());
            if ($pendingTransfers->isNotEmpty()) {
                $this->table(
                    ['invoice_id', 'service_provider_id', 'amount', 'created_at'],
                    $pendingTransfers->map(fn ($invoice) => [
                        $invoice->id,
                        $invoice->service_provider_id,
                        (float) $invoice->amount,
                        (string) $invoice->created_at,
                    ])->toArray(),
                );
            }
        }

        return self::SUCCESS;
    }
}
