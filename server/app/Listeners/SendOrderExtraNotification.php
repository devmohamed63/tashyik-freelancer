<?php

namespace App\Listeners;

use App\Events\NewOrderExtra;
use App\Utils\Traits\Listeners\HasNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderExtraNotification
{
    use HasNotifications;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NewOrderExtra $event): void
    {
        $title = [
            'ar' => 'طلب دفع للخدمات الإضافية',
            'en' => 'Payment request for additional services',
            'hi' => 'अतिरिक्त सेवाओं के लिए भुगतान अनुरोध',
            'bn' => 'অতিরিক্ত পরিষেবার জন্য অর্থ প্রদানের অনুরোধ',
            'ur' => 'اضافی خدمات کے لیے ادائیگی کی درخواست',
            'tl' => 'Request ng pagbabayad para sa karagdagang serbisyo',
            'id' => 'Permintaan pembayaran untuk layanan tambahan',
            'fr' => 'Demande de paiement pour services supplémentaires',
        ];

        $description = [
            'ar' => 'أصدر الفني فاتورة بالخدمات الإضافية، يُرجى السداد لاستكمال الطلب.',
            'en' => 'The technician has issued a bill for additional services, please make the payment to proceed.',
            'hi' => 'तकनीशियन ने अतिरिक्त सेवाओं के लिए बिल जारी किया है, कृपया आगे बढ़ने के लिए भुगतान करें।',
            'bn' => 'প্রযুক্তিবিদ অতিরিক্ত পরিষেবার জন্য বিল জারি করেছেন, অনুগ্রহ করে অগ্রগতি চালিয়ে যেতে অর্থ প্রদান করুন।',
            'ur' => 'فنی نے اضافی خدمات کے لیے بل جاری کیا ہے، براہ کرم آگے بڑھنے کے لیے ادائیگی کریں۔',
            'tl' => 'Nag-isyu ang technician ng bill para sa karagdagang serbisyo, mangyaring magbayad upang ipagpatuloy.',
            'id' => 'Teknisi telah mengeluarkan tagihan untuk layanan tambahan, silakan lakukan pembayaran untuk melanjutkan.',
            'fr' => 'Le technicien a émis une facture pour les services supplémentaires, veuillez effectuer le paiement pour continuer.',
        ];

        $type = 'order-completed';

        $this->sendNotification(
            type: $type,
            title: $title,
            description: $description,
            pushNotification: true,
            recipient: $event->orderExtra->order?->customer,
            data: [
                'order' => [
                    'id' => $event->orderExtra->order?->id,
                ],
            ],
        );
    }
}
