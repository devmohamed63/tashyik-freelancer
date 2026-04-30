<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>فاتورة دفترة</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f5;direction:rtl;text-align:right;font-family:system-ui,-apple-system,'Segoe UI',Roboto,'Tahoma',sans-serif;line-height:1.65;color:#1a1a1a;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" dir="rtl" bgcolor="#f4f4f5" style="direction:rtl;text-align:right;background:#f4f4f5;padding:24px 12px;">
    <tr>
        <td align="right" valign="top" dir="rtl" style="direction:rtl;text-align:right;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" align="right" dir="rtl" style="max-width:520px;width:100%;direction:rtl;text-align:right;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.08);">
                <tr>
                    <td dir="rtl" align="right" style="padding:28px 24px 8px;direction:rtl;text-align:right;">
                        <p style="margin:0;font-size:13px;color:#6b7280;">Tashyik</p>
                        <h1 style="margin:8px 0 0;font-size:22px;font-weight:700;">فاتورتك في دفترة</h1>
                        @if(! empty($daftraPdfBinary) && str_starts_with(ltrim($daftraPdfBinary, " \t\r\n"), '%PDF'))
                            <p style="margin:10px 0 0;font-size:14px;color:#166534;background:#ecfdf5;padding:10px 12px;border-radius:8px;">
                                مُرفق مع هذه الرسالة ملف <strong>PDF</strong> للفاتورة كما صدر من دفترة (نفس المستند في النظام المحاسبي).
                            </p>
                        @endif
                        @php
                            $primaryHrefIntro = $invoice->invoiceEmailPrimaryUrl();
                            $recipientHref = $invoice->daftraRecipientWebUrl();
                            $introUsesDaftraPrimary = $recipientHref !== null && $primaryHrefIntro === $recipientHref;
                            $includeLocalPublic = (bool) config('services.tashyik.invoice_emails_include_local_public_link', true);
                        @endphp
                        <p style="margin:12px 0 0;font-size:15px;color:#4b5563;">
                            @if($introUsesDaftraPrimary)
                                تمت مزامنة فاتورتك مع <strong>دفترة</strong>. الزر الأزرق يفتح صفحة عرض الفاتورة في منطقة العميل على دفترة (<code dir="ltr" style="font-size:12px;">/client/invoices/view/</code>) ولا يتطلب تسجيل الدخول بحساب الشركة. رابط «لوحة دفترة» أدناه للمحاسبة فقط ويتطلب الدخول بحساب الشركة.
                            @elseif($includeLocalPublic)
                                تمت مزامنة فاتورتك مع <strong>دفترة</strong>. الزر الأزرق يفتح صفحة للاطلاع على نفس السجل على Tashyik حتى يُسجَّل رقم الفاتورة في دفترة ويظهر رابط العرض هناك. رابط «لوحة دفترة» أدناه للمحاسبة ويتطلب تسجيل الدخول بحساب الشركة في دفترة.
                            @else
                                تمت مزامنة فاتورتك مع <strong>دفترة</strong>. لا نُرفق نسخة فاتورة كملف على سيرفر Tashyik — بعد تسجيل رقم الفاتورة في دفترة سيظهر الزر أدناه لعرضها في دفترة. رابط «لوحة دفترة» للمحاسبة ويتطلب تسجيل الدخول بحساب الشركة في دفترة.
                            @endif
                        </p>
                    </td>
                </tr>
                <tr>
                    <td dir="rtl" align="right" style="padding:8px 24px 24px;direction:rtl;text-align:right;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" dir="rtl" style="border-collapse:collapse;font-size:14px;direction:rtl;text-align:right;">
                            @include('emails.partials.invoice-email-daftra-block', ['invoice' => $invoice])
                        </table>
                    </td>
                </tr>
            </table>
            <p dir="rtl" style="margin:16px auto 0;max-width:520px;font-size:12px;color:#9ca3af;text-align:right;">هذه رسالة تلقائية، يرجى عدم الرد على هذا البريد.</p>
        </td>
    </tr>
</table>
</body>
</html>
