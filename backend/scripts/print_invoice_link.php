<?php

declare(strict_types=1);

/**
 * طباعة روابط الفاتورة: رابط العميل /client/invoices/view/{id} (كالإيميل) + تشخيص JSON + لوحة دفترة + Tashyik.
 *
 *   php scripts/print_invoice_link.php                    آخر فاتورة محلية لها daftra_id
 *   php scripts/print_invoice_link.php 12345              فاتورة المنصة رقم 12345
 *   php scripts/print_invoice_link.php --daftra-id=18   فاتورة دفترة رقم 18 مباشرة (بدون صف محلي)
 */

use App\Models\Invoice;
use App\Models\User;
use App\Utils\Services\Daftra;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$daftra = app(Daftra::class);

$argv = $_SERVER['argv'] ?? [];
$daftraOnlyId = null;
$tashyikInvoiceId = null;

foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--daftra-id=')) {
        $daftraOnlyId = (int) substr($arg, strlen('--daftra-id='));
    } elseif (ctype_digit($arg)) {
        $tashyikInvoiceId = (int) $arg;
    }
}

if ($daftraOnlyId !== null && $daftraOnlyId > 0) {
    fwrite(STDOUT, "=== فاتورة دفترة #{$daftraOnlyId} ===\n");
    fwrite(STDOUT, 'رابط العميل (كما في إيميلات التطبيق): '.$daftra->clientInvoiceViewUrl($daftraOnlyId)."\n");
    $d = $daftra->getSalesInvoiceFetchDiagnostics($daftraOnlyId);
    $json = ($d['http_ok'] && is_array($d['payload'])) ? $d['payload'] : null;
    if ($json === null) {
        fwrite(STDERR, "\nتعذر جلب JSON من دفترة (للتشخيص فقط).\n");
        fwrite(STDERR, 'GET '.$d['request_url']."\n");
        if (! $d['configured']) {
            fwrite(STDERR, ($d['api_message'] ?? 'DAFTRA_API_KEY')."\n");
        } else {
            fwrite(STDERR, sprintf('HTTP %d', $d['http_status']).($d['api_message'] ? ' — '.$d['api_message'] : '')."\n");
            if ($d['http_status'] === 404) {
                fwrite(STDERR, "تلميح: 404 = الفاتورة غير موجودة على هذا الحساب أو المعرف خطأ.\n");
            }
        }
        fwrite(STDOUT, 'لوحة دفترة (يتطلب دخول شركة): '.$daftra->ownerInvoiceViewUrl($daftraOnlyId)."\n");
        fwrite(STDOUT, "\nphp artisan daftra:invoice-json {$daftraOnlyId} --resolve\n");
        exit(1);
    }
    $recipient = $daftra->resolveRecipientViewUrlFromPayload($json);
    $pdfUrl = $daftra->resolveSalesInvoicePdfUrlFromPayload($json);
    fwrite(STDOUT, 'من JSON (public_link / PDF — تشخيص): '.($recipient ?? '(لا يوجد في الاستجابة)')."\n");
    fwrite(STDOUT, 'رابط PDF من JSON (إن وُجد): '.($pdfUrl ?? '(لا يوجد)')."\n");
    fwrite(STDOUT, 'لوحة دفترة (يتطلب دخول شركة): '.$daftra->ownerInvoiceViewUrl($daftraOnlyId)."\n");
    fwrite(STDOUT, "\nللتشخيص الكامل:\nphp artisan daftra:invoice-json {$daftraOnlyId} --resolve\n");
    exit(0);
}

if ($tashyikInvoiceId !== null) {
    $i = Invoice::query()->find($tashyikInvoiceId);
    if (! $i) {
        fwrite(STDERR, "لا توجد فاتورة محلية #{$tashyikInvoiceId}.\n");
        exit(1);
    }
} else {
    $i = Invoice::query()->whereNotNull('daftra_id')->orderByDesc('id')->first();
    if (! $i) {
        $u = User::query()->where('type', User::SERVICE_PROVIDER_ACCOUNT_TYPE)->first()
            ?? User::factory()->create(['type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE]);
        $i = Invoice::factory()->create(['service_provider_id' => $u->id]);
        $i->refresh();
        fwrite(STDOUT, "(تحذير: لا توجد فاتورة بـ daftra_id؛ أُنشئت فاتورة تجريبية محلية #{$i->id} بدون دفترة)\n\n");
    }
}

fwrite(STDOUT, "=== فاتورة المنصة #{$i->id} ===\n");
fwrite(STDOUT, 'Tashyik (صفحة عامة): '.$i->platformWebUrl()."\n");

if ($i->daftra_id) {
    fwrite(STDOUT, "\n=== دفترة #{$i->daftra_id} ===\n");
    fwrite(STDOUT, 'رابط العميل (كما في الإيميل): '.$daftra->clientInvoiceViewUrl((int) $i->daftra_id)."\n");
    $d = $daftra->getSalesInvoiceFetchDiagnostics((int) $i->daftra_id);
    $json = ($d['http_ok'] && is_array($d['payload'])) ? $d['payload'] : null;
    if ($json === null) {
        fwrite(STDOUT, "تعذر جلب JSON من API (للتشخيص فقط).\n");
        fwrite(STDOUT, sprintf('HTTP %d', $d['http_status']).($d['api_message'] ? ' — '.$d['api_message'] : '')."\n");
        fwrite(STDOUT, 'لوحة دفترة: '.$daftra->ownerInvoiceViewUrl((int) $i->daftra_id)."\n");
    } else {
        $recipient = $daftra->resolveRecipientViewUrlFromPayload($json);
        $pdfUrl = $daftra->resolveSalesInvoicePdfUrlFromPayload($json);
        fwrite(STDOUT, 'من JSON (تشخيص): '.($recipient ?? '(لا يوجد)')."\n");
        fwrite(STDOUT, 'رابط PDF من JSON: '.($pdfUrl ?? '(لا يوجد)')."\n");
        fwrite(STDOUT, 'لوحة دفترة (دخول شركة): '.$daftra->ownerInvoiceViewUrl((int) $i->daftra_id)."\n");
        if ($i->daftra_public_view_url) {
            fwrite(STDOUT, 'المخزّن في DB (daftra_public_view_url، من API): '.$i->daftra_public_view_url."\n");
        }
    }
    fwrite(STDOUT, "\nphp artisan daftra:invoice-json {$i->daftra_id} --resolve\n");
} else {
    fwrite(STDOUT, "\n(لا يوجد daftra_id — زامن الفاتورة مع دفترة ثم أعد التشغيل.)\n");
}
