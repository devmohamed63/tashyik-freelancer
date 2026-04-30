<?php

namespace App\Console\Commands;

use App\Jobs\SendDaftraInvoicePdfMailJob;
use App\Models\Invoice;
use Illuminate\Console\Command;

class DaftraResendInvoicePdfCommand extends Command
{
    protected $signature = 'daftra:resend-invoice-pdf
                            {invoice_id : Local Tashyik invoice id}
                            {--force : Required if the Daftra link email was already sent (clears daftra_invoice_pdf_sent_at)}';

    protected $description = 'إعادة إرسال بريد رابط فاتورة دفترة للفني (To) ونسخة BCC من DAFTRA_INVOICE_PDF_BCC_EMAIL إن وُجدت. استخدم --force بعد إرسال سابق.';

    public function handle(): int
    {
        if (! (bool) config('services.daftra.invoice_pdf_enabled')) {
            $this->error('DAFTRA_INVOICE_PDF_EMAIL_ENABLED غير مفعّل.');

            return self::FAILURE;
        }

        $id = (int) $this->argument('invoice_id');
        if ($id < 1) {
            $this->error('invoice_id غير صالح.');

            return self::FAILURE;
        }

        $invoice = Invoice::query()->with('serviceProvider')->find($id);
        if (! $invoice) {
            $this->error("لا توجد فاتورة #{$id}.");

            return self::FAILURE;
        }

        if (! $invoice->daftra_id) {
            $this->error('الفاتورة بدون daftra_id — زامِن أولاً.');

            return self::FAILURE;
        }

        $syncable = [
            Invoice::COMPLETED_ORDER_TYPE,
            Invoice::RENEW_SUBSCRIPTION_TYPE,
            Invoice::ADDITIONAL_SERVICES_TYPE,
        ];
        if (! in_array($invoice->type, $syncable, true)) {
            $this->error('نوع الفاتورة لا يدعم بريد رابط دفترة لهذا المسار.');

            return self::FAILURE;
        }

        $to = $invoice->serviceProvider?->email;
        if (! $to || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error('بريد مقدّم الخدمة غير صالح أو غير موجود.');

            return self::FAILURE;
        }

        if ($invoice->daftra_invoice_pdf_sent_at !== null && ! $this->option('force')) {
            $this->error('تم إرسال بريد رابط دفترة من قبل. أعد التشغيل مع --force لمسح الطابع وإعادة الإرسال.');

            return self::FAILURE;
        }

        if ($this->option('force')) {
            $invoice->forceFill(['daftra_invoice_pdf_sent_at' => null])->save();
            $this->info('تم مسح daftra_invoice_pdf_sent_at.');
        }

        $this->info("إرسال إلى: {$to}");
        $bcc = config('services.daftra.invoice_pdf_bcc');
        if (is_string($bcc) && $bcc !== '' && filter_var($bcc, FILTER_VALIDATE_EMAIL)) {
            $this->line('BCC: '.$bcc);
        }

        try {
            (new SendDaftraInvoicePdfMailJob($invoice->id))->handle();
        } catch (\Throwable $e) {
            $this->error('فشل الجوب: '.$e->getMessage());

            return self::FAILURE;
        }

        $invoice->refresh();
        if ($invoice->daftra_invoice_pdf_sent_at === null) {
            $this->warn('لم يُضبط daftra_invoice_pdf_sent_at — راجع السجلات (تخطي أو فشل SMTP/Daftra).');

            return self::FAILURE;
        }

        $this->info('تم الإرسال بنجاح.');

        return self::SUCCESS;
    }
}
