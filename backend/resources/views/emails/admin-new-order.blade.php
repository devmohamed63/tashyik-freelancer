<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إشعار طلب جديد</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; direction: rtl; text-align: right;">
    <div style="background-color: #ffffff; padding: 30px; border-radius: 10px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-top: 5px solid #0d9488;">
        
        <h2 style="color: #111827; font-size: 24px; margin-top: 0; margin-bottom: 20px;">
            طلب خدمة جديد 🚀
        </h2>
        
        <p style="color: #4b5563; font-size: 16px; line-height: 1.6; margin-bottom: 25px;">
            صاحب السعادة، لقد تم إنشاء طلب جديد من قِبل عميل في التطبيق. إليك التفاصيل:
        </p>

        <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
            <table style="width: 100%; border-collapse: collapse; direction: rtl;">
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; width: 35%; color: #6b7280; font-weight: bold; text-align: right;">رقم الطلب:</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #111827; text-align: right; font-weight: 500;">#{{ $order->id }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-weight: bold; text-align: right;">الخدمة:</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #111827; text-align: right;">{{ $order->service->name ?? 'غير متوفر' }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-weight: bold; text-align: right;">القسم:</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #111827; text-align: right;">{{ $order->category->name ?? 'غير متوفر' }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-weight: bold; text-align: right;">العميل:</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #111827; text-align: right;">{{ $order->customer->name ?? 'مستخدم' }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-weight: bold; text-align: right;">الكمية:</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #111827; text-align: right;">{{ $order->quantity }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-weight: bold; text-align: right;">الإجمالي:</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #0d9488; text-align: right; font-weight: bold;">{{ number_format($order->total, 2) }} {{ __('ui.currency') }}</td>
                </tr>
            </table>
        </div>

        @if($order->description)
        <h3 style="color: #374151; font-size: 18px; margin-bottom: 10px;">وصف المشكلة/الطلب:</h3>
        <div style="background-color: #f3f4f6; border-right: 4px solid #0d9488; padding: 20px; border-radius: 4px 8px 8px 4px; color: #1f2937; line-height: 1.6; font-size: 16px; white-space: pre-wrap;">{{ $order->description }}</div>
        @endif

        <div style="margin-top: 30px; text-align: center;">
            <a href="{{ url('dashboard/orders?id=' . $order->id) }}" style="background-color: #0d9488; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">عرض الطلب في الداش بورد</a>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center;">
            <p style="margin: 0; font-size: 13px; color: #9ca3af;">
                هذا الإشعار تلقائي لمدير النظام وتم توليده من التطبيق وقت الإنشاء.
            </p>
        </div>
    </div>
</body>
</html>
