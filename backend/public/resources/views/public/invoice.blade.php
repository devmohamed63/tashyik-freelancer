<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>فاتورة Tashyik #{{ $invoice->id }}</title>
</head>
<body style="font-family: system-ui, sans-serif; line-height: 1.6; color: #111; max-width: 520px; margin: 24px auto; padding: 0 16px; direction: rtl; text-align: right;">
    <h1 style="font-size: 1.25rem; margin: 0 0 16px;">فاتورة المنصة (Tashyik)</h1>
    <p style="margin: 0 0 8px;"><strong>رقم الفاتورة:</strong> #{{ $invoice->id }}</p>
    <p style="margin: 0 0 8px;"><strong>النوع:</strong> {{ $invoice->translated_type }}</p>
    <p style="margin: 0 0 8px;"><strong>المبلغ:</strong> {{ number_format((float) $invoice->amount, 2) }} {{ __('ui.currency') }}</p>
    <p style="margin: 0 0 8px;"><strong>التاريخ:</strong> {{ $invoice->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</p>
    @if($invoice->daftra_id)
        <p style="margin: 0 0 8px;"><strong>رقم دفترة:</strong> #{{ $invoice->daftra_id }}</p>
    @endif
    @if($invoice->serviceProvider)
        <p style="margin: 0 0 8px;"><strong>مقدّم الخدمة:</strong> {{ $invoice->serviceProvider->name }}</p>
    @endif
    <p style="margin: 24px 0 0; font-size: 0.85rem; color: #666;">هذه صفحة للاطلاع فقط. الرابط موقّع ولا يتطلب تسجيل الدخول.</p>
</body>
</html>
