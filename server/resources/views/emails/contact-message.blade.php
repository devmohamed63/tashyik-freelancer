<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رسالة تواصل جديدة</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; direction: rtl; text-align: right;">
    <div style="background-color: #ffffff; padding: 30px; border-radius: 10px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-top: 5px solid #0d9488;">
        
        <h2 style="color: #111827; font-size: 24px; margin-top: 0; margin-bottom: 20px;">
            أهلاً، لديك طلب تواصل جديد 🎉
        </h2>
        
        <p style="color: #4b5563; font-size: 16px; line-height: 1.6; margin-bottom: 25px;">
            لقد قام أحد الزوار بإرسال رسالة تواصل جديدة من خلال موقعك، وإليك التفاصيل:
        </p>

        <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
            <table style="width: 100%; border-collapse: collapse; direction: rtl;">
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; width: 35%; color: #6b7280; font-weight: bold; text-align: right;">الإسم:</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #111827; text-align: right; font-weight: 500;">{{ $contact->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-weight: bold; text-align: right;">البريد الإلكتروني:</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #111827; text-align: right;"><a href="mailto:{{ $contact->email }}" style="color: #0d9488; text-decoration: none;">{{ $contact->email ?? 'غير محدد' }}</a></td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-weight: bold; text-align: right;">الهاتف:</td>
                    <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb; color: #111827; text-align: right; direction: ltr; display: inline-block;">{{ $contact->phone }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; color: #6b7280; font-weight: bold; text-align: right;">الموضوع:</td>
                    <td style="padding: 10px 0; color: #111827; text-align: right; font-weight: 500;">{{ $contact->subject ?? 'تواصل عام' }}</td>
                </tr>
            </table>
        </div>

        <h3 style="color: #374151; font-size: 18px; margin-bottom: 10px;">نص الرسالة:</h3>
        
        <div style="background-color: #f3f4f6; border-right: 4px solid #0d9488; padding: 20px; border-radius: 4px 8px 8px 4px; color: #1f2937; line-height: 1.6; font-size: 16px; white-space: pre-wrap;">{{ $contact->message }}</div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center;">
            <p style="margin: 0; font-size: 13px; color: #9ca3af;">
                هذا البريد تم إرساله تلقائياً من نظام المنصة. يُرجى عدم الرد عليه.
            </p>
        </div>
    </div>
</body>
</html>
