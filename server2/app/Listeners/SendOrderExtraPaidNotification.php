<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\OrderExtraPaid;
use App\Utils\Traits\Listeners\HasNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderExtraPaidNotification
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
    public function handle(OrderExtraPaid $event): void
    {
        $title = [
            'ar' => 'دفع العميل تكلفة الخدمات الإضافية',
            'en' => 'Customer paid for additional services',
            'hi' => 'ग्राहक ने अतिरिक्त सेवाओं का भुगतान किया',
            'bn' => 'গ্রাহক অতিরিক্ত পরিষেবার জন্য অর্থ প্রদান করেছেন',
            'ur' => 'گاہک نے اضافی خدمات کی ادائیگی کر دی',
            'tl' => 'Nagbayad ang customer para sa karagdagang serbisyo',
            'id' => 'Pelanggan telah membayar layanan tambahan',
            'fr' => 'Le client a payé pour les services supplémentaires',
        ];

        $description = [
            'ar' => 'دفع العميل رسوم الخدمات الإضافية, طلبك جاهز للمتابعة.',
            'en' => 'The customer has paid for additional services, your request is ready to proceed.',
            'hi' => 'ग्राहक ने अतिरिक्त सेवाओं का भुगतान कर दिया है, आपका अनुरोध आगे बढ़ने के लिए तैयार है।',
            'bn' => 'গ্রাহক অতিরিক্ত পরিষেবার জন্য অর্থ প্রদান করেছেন, আপনার অনুরোধ সম্পাদনের জন্য প্রস্তুত।',
            'ur' => 'گاہک نے اضافی خدمات کی ادائیگی کر دی ہے، آپ کی درخواست آگے بڑھانے کے لیے تیار ہے۔',
            'tl' => 'Nagbayad ang customer para sa karagdagang serbisyo, handa na ang iyong kahilingan na iproseso.',
            'id' => 'Pelanggan telah membayar layanan tambahan, permintaan Anda siap untuk diproses.',
            'fr' => 'Le client a payé pour les services supplémentaires, votre demande est prête à être traitée.',
        ];

        $type = 'order-extra-paid';

        $serviceProvider = User::find($event->data['service_provider_id']);

        $this->sendNotification(
            type: $type,
            title: $title,
            description: $description,
            pushNotification: true,
            recipient: $serviceProvider,
            data: [
                'order' => [
                    'id' => $event->data['order_id'],
                ],
            ],
        );
    }
}
