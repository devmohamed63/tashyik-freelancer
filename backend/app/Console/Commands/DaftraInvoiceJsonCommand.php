<?php

namespace App\Console\Commands;

use App\Utils\Services\Daftra;
use Illuminate\Console\Command;

/**
 * Inspect raw Daftra GET invoices/{id}.json to find public_link / share_link / customer_view_url / invoice_pdf_url, etc.
 */
class DaftraInvoiceJsonCommand extends Command
{
    protected $signature = 'daftra:invoice-json
                            {id : رقم فاتورة المبيعات في دفترة (daftra_id)}
                            {--resolve : اطبع أيضاً الرابط الذي يختاره التطبيق للعميل/الإيميل}';

    protected $description = 'طباعة JSON كامل لفاتورة دفترة (GET api2/invoices/{id}.json) — للبحث عن public_link وغيره';

    public function handle(Daftra $daftra): int
    {
        $id = (int) $this->argument('id');
        if ($id < 1) {
            $this->error('معرف غير صالح.');

            return self::FAILURE;
        }

        $diag = $daftra->getSalesInvoiceFetchDiagnostics($id);
        $payload = ($diag['http_ok'] && is_array($diag['payload'])) ? $diag['payload'] : null;
        if ($payload === null) {
            $this->error('لم يُرجع الـ API فاتورة صالحة لهذا المعرف.');
            $this->line('GET '.$diag['request_url']);
            if (! $diag['configured']) {
                $this->warn($diag['api_message'] ?? 'اضبط DAFTRA_API_KEY في .env');
            } else {
                $this->warn(sprintf('HTTP %d', $diag['http_status']).($diag['api_message'] ? ' — '.$diag['api_message'] : ''));
                if ($diag['http_status'] === 404) {
                    $this->comment('404 غالباً: الفاتورة غير موجودة على نفس حساب DAFTRA_SUBDOMAIN، أو الرقم ليس daftra_id الصحيح من لوحة دفترة.');
                }
                if (in_array($diag['http_status'], [401, 403], true)) {
                    $this->comment('تحقق من صلاحية DAFTRA_API_KEY أو أن المفتاح لهذا الـ subdomain.');
                }
            }
            $this->comment('للتفاصيل التقنية: storage/logs/laravel.log');

            return self::FAILURE;
        }

        $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->newLine();
        $this->comment('ابحث في المخرجات عن: public_link, share_link, customer_view_url, invoice_html_url, invoice_pdf_url, …');
        $this->comment('روابط مسار /owner/ مخصصة لحساب الشركة وليست رابط عميل.');
        $this->comment('رابط الإيميلات (ثابت، منطقة العميل): '.$daftra->clientInvoiceViewUrl($id));

        if ($this->option('resolve')) {
            $this->newLine();
            $resolved = $daftra->resolveRecipientViewUrlFromPayload($payload);
            $this->info('من JSON (تجاهل /owner/): '.($resolved ?? '(لا يوجد — راجع الحقول أعلاه أو مرفق PDF)'));
        }

        return self::SUCCESS;
    }
}
