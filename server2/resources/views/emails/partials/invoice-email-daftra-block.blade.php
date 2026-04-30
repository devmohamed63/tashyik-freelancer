{{--
  Primary CTA: Invoice::invoiceEmailPrimaryUrl() — Daftra /client/… when daftra_id, rare stored API URL, else Tashyik. Mirror + owner link below.
--}}
@php
    $daftra = app(\App\Utils\Services\Daftra::class);
    $includeLocalPublic = (bool) config('services.tashyik.invoice_emails_include_local_public_link', true);
    $primaryHref = $invoice->invoiceEmailPrimaryUrl();
    $primaryIsDaftra = $invoice->daftraRecipientWebUrl() !== null
        && $primaryHref === $invoice->daftraRecipientWebUrl();
    $platformHref = $includeLocalPublic ? $invoice->platformWebUrl() : null;
    $ownerDaftraHref = $invoice->daftra_id
        ? $daftra->ownerInvoiceViewUrl((int) $invoice->daftra_id)
        : $daftra->ownerInvoicesIndexUrl();
@endphp
<tr>
    <td colspan="2" dir="rtl" align="right" style="padding:14px 0 8px;border-bottom:1px solid #eee;text-align:right;vertical-align:middle;font-size:14px;">
        @if($primaryHref)
            <a href="{{ $primaryHref }}"
               target="_blank"
               rel="noopener noreferrer"
               style="display:inline-block;padding:12px 20px;background:#2563eb;color:#ffffff !important;text-decoration:none;border-radius:10px;font-weight:700;font-size:15px;text-align:center;">
                {{ $primaryIsDaftra ? 'عرض الفاتورة في دفترة' : 'فتح الفاتورة (منصة Tashyik)' }}
            </a>
        @else
            <span dir="rtl" style="display:inline-block;padding:12px 16px;background:#f3f4f6;color:#374151;border-radius:10px;font-weight:600;font-size:14px;text-align:center;">
                رابط عرض الفاتورة من دفترة غير متاح بعد — سيتم تحديث الإيميل أو إشعارك عند اكتمال المزامنة.
            </span>
        @endif
        <span dir="rtl" style="display:inline-block;margin-inline-start:10px;font-size:13px;color:#6b7280;vertical-align:middle;">رقم المنصة <span dir="ltr" style="unicode-bidi:isolate;">#{{ $invoice->id }}</span></span>
        @if($invoice->daftra_id)
            <span dir="ltr" style="unicode-bidi:isolate;display:inline-block;margin-inline-start:8px;font-size:13px;color:#6b7280;vertical-align:middle;">دفترة #{{ $invoice->daftra_id }}</span>
        @endif
        @if($includeLocalPublic && $primaryIsDaftra && $platformHref)
            <p dir="rtl" style="margin:12px 0 0;font-size:13px;color:#4b5563;text-align:right;line-height:1.5;">
                <a href="{{ $platformHref }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   style="font-weight:600;color:#2563eb;text-decoration:underline;">
                    نفس الفاتورة على منصة Tashyik
                </a>
            </p>
        @endif
        <p dir="rtl" style="margin:12px 0 0;font-size:13px;color:#4b5563;text-align:right;line-height:1.5;">
            <a href="{{ $ownerDaftraHref }}"
               target="_blank"
               rel="noopener noreferrer"
               style="font-weight:600;color:#2563eb;text-decoration:underline;">
                لوحة دفترة (حساب الشركة)
            </a>
            <span style="color:#9ca3af;"> — يتطلب تسجيل الدخول</span>
        </p>
        @if(! $invoice->daftra_id)
            <p dir="rtl" style="margin:8px 0 0;font-size:12px;color:#9ca3af;text-align:right;">يُضاف رقم الفاتورة في دفترة تلقائياً بعد اكتمال المزامنة؛ ثم يظهر زر عرض الفاتورة في دفترة هنا.</p>
        @endif
    </td>
</tr>
