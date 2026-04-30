<?php

namespace App\Listeners;

use App\Events\ServiceProviderArrived;
use App\Utils\Traits\Listeners\HasNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendServiceProviderArrivedNotification
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
    public function handle(ServiceProviderArrived $event): void
    {
        $title = [
            'ar' => 'وصل الفني لموقع الخدمة',
            'en' => 'Technician arrived at the service location',
            'hi' => 'तकनीशियन सेवा स्थान पर पहुंच गया है',
            'bn' => 'টেকনিশিয়ান সেবার স্থানে পৌঁছেছেন',
            'ur' => 'ٹیکنیشن سروس کی جگہ پر پہنچ گیا ہے',
            'tl' => 'Dumating na ang technician sa lokasyon ng serbisyo',
            'id' => 'Teknisi telah tiba di lokasi layanan',
            'fr' => 'Le technicien est arrivé sur le lieu de service',
        ];

        $description = [
            'ar' => 'الفني وصل الموقع المحدد، وجاهز لبدء تنفيذ الخدمة.',
            'en' => 'The technician has arrived at the specified location and is ready to start the service.',
            'hi' => 'तकनीशियन निर्दिष्ट स्थान पर पहुंच गया है और सेवा शुरू करने के लिए तैयार है।',
            'bn' => 'টেকনিশিয়ান নির্দিষ্ট স্থানে পৌঁছেছেন এবং সেবা শুরু করার জন্য প্রস্তুত।',
            'ur' => 'ٹیکنیشن مخصوص مقام پر پہنچ گیا ہے اور خدمت شروع کرنے کے لیے تیار ہے۔',
            'tl' => 'Ang technician ay dumating na sa tinukoy na lokasyon at handa nang simulan ang serbisyo.',
            'id' => 'Teknisi telah tiba di lokasi yang ditentukan dan siap untuk memulai layanan.',
            'fr' => 'Le technicien est arrivé à l\'endroit spécifié et est prêt à commencer le service.',
        ];

        $type = 'service-provider-arrived';

        $this->sendNotification(
            type: $type,
            title: $title,
            description: $description,
            pushNotification: true,
            recipient: $event->order->customer,
            data: [
                'order' => [
                    'id' => $event->order->id,
                ],
            ],
        );
    }
}
