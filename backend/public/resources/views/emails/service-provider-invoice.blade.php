@php
    use App\Models\Invoice;

    $sp = $invoice->serviceProvider;

    [$pageTitle, $heading, $lead] = match ($invoice->type) {
        Invoice::ADDITIONAL_SERVICES_TYPE => [
            'خدمات إضافية',
            'تم تسجيل رصيد خدمات إضافية',
            'تمت إضافة مبلغ الخدمات الإضافية إلى رصيدك وفق الطلب المشار إليه. يمكنك مراجعة التفاصيل في تطبيق Tashyik في أي وقت.',
        ],
        Invoice::COMPLETED_ORDER_TYPE => [
            'طلب مكتمل',
            'تم تسجيل رصيد طلب مكتمل',
            'تم تسجيل مستحقاتك عن طلب مكتمل. يمكنك مراجعة التفاصيل في تطبيق Tashyik في أي وقت.',
        ],
        Invoice::BANK_TRANSFER_TYPE => [
            'تحويل بنكي',
            'تم تسجيل خصم تحويل بنكي',
            'تم تسجيل عملية تحويل بنكي على رصيدك. يمكنك مراجعة التفاصيل في تطبيق Tashyik في أي وقت.',
        ],
        default => [
            'فاتورة',
            'إشعار فاتورة جديد',
            'تم إصدار سجل مالي جديد على حسابك في Tashyik.',
        ],
    };
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f5;direction:rtl;text-align:right;font-family:system-ui,-apple-system,'Segoe UI',Roboto,'Tahoma',sans-serif;line-height:1.65;color:#1a1a1a;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" dir="rtl" bgcolor="#f4f4f5" style="direction:rtl;text-align:right;background:#f4f4f5;padding:24px 12px;">
    <tr>
        <td align="right" valign="top" dir="rtl" style="direction:rtl;text-align:right;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" align="right" dir="rtl" style="max-width:520px;width:100%;direction:rtl;text-align:right;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.08);">
                <tr>
                    <td dir="rtl" align="right" style="padding:28px 24px 8px;direction:rtl;text-align:right;">
                        <p style="margin:0;font-size:13px;color:#6b7280;text-align:right;direction:rtl;">Tashyik</p>
                        <h1 style="margin:8px 0 0;font-size:22px;font-weight:700;text-align:right;direction:rtl;">{{ $heading }}</h1>
                        <p style="margin:12px 0 0;font-size:15px;color:#4b5563;text-align:right;direction:rtl;">
                            @if($sp?->name)
                                مرحباً <span dir="ltr" style="unicode-bidi:isolate;display:inline-block;">{{ $sp->name }}</span>،
                            @else
                                مرحباً،
                            @endif
                        </p>
                        <p style="margin:8px 0 0;font-size:15px;color:#4b5563;text-align:right;direction:rtl;">{{ $lead }}</p>
                    </td>
                </tr>
                <tr>
                    <td dir="rtl" align="right" style="padding:8px 24px 24px;direction:rtl;text-align:right;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" dir="rtl" style="border-collapse:collapse;font-size:14px;direction:rtl;text-align:right;">
                            <tr>
                                <td dir="rtl" align="right" style="padding:10px 0 10px 8px;border-bottom:1px solid #eee;font-weight:600;width:42%;text-align:right;vertical-align:top;">نوع الفاتورة</td>
                                <td dir="rtl" align="right" style="padding:10px 0;border-bottom:1px solid #eee;text-align:right;vertical-align:top;">{{ $invoice->translated_type }}</td>
                            </tr>
                            <tr>
                                <td dir="rtl" align="right" style="padding:10px 0 10px 8px;border-bottom:1px solid #eee;font-weight:600;text-align:right;vertical-align:top;">الإجراء</td>
                                <td dir="rtl" align="right" style="padding:10px 0;border-bottom:1px solid #eee;text-align:right;vertical-align:top;">{{ $invoice->action === Invoice::CREDIT_ACTION ? 'دائن' : 'مدين' }}</td>
                            </tr>
                            <tr>
                                <td dir="rtl" align="right" style="padding:10px 0 10px 8px;border-bottom:1px solid #eee;font-weight:600;text-align:right;vertical-align:top;">المبلغ</td>
                                <td dir="rtl" align="right" style="padding:10px 0;border-bottom:1px solid #eee;text-align:right;vertical-align:top;"><span dir="ltr" style="unicode-bidi:isolate;display:inline-block;">{{ number_format((float) $invoice->amount, 2) }}</span> {{ __('ui.currency') }}</td>
                            </tr>
                            @if($invoice->target_id)
                            <tr>
                                <td dir="rtl" align="right" style="padding:10px 0 10px 8px;border-bottom:1px solid #eee;font-weight:600;text-align:right;vertical-align:top;">مرجع الطلب</td>
                                <td dir="rtl" align="right" style="padding:10px 0;border-bottom:1px solid #eee;text-align:right;vertical-align:top;"><span dir="ltr" style="unicode-bidi:isolate;display:inline-block;">#{{ $invoice->target_id }}</span></td>
                            </tr>
                            @endif
                            <tr>
                                <td dir="rtl" align="right" style="padding:10px 0 10px 8px;border-bottom:1px solid #eee;font-weight:600;text-align:right;vertical-align:top;">تاريخ الإصدار</td>
                                <td dir="rtl" align="right" style="padding:10px 0;border-bottom:1px solid #eee;text-align:right;vertical-align:top;"><span dir="ltr" style="unicode-bidi:isolate;display:inline-block;">{{ $invoice->created_at?->format('Y-m-d H:i') }}</span></td>
                            </tr>
                            @include('emails.partials.invoice-email-daftra-block', ['invoice' => $invoice])
                        </table>
                    </td>
                </tr>
            </table>
            <p dir="rtl" style="margin:16px auto 0;max-width:520px;font-size:12px;color:#9ca3af;text-align:right;direction:rtl;">هذه رسالة تلقائية، يرجى عدم الرد على هذا البريد.</p>
        </td>
    </tr>
</table>
</body>
</html>
