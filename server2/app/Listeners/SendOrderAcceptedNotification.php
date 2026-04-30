<?php

namespace App\Listeners;

use App\Events\OrderAccepted;
use App\Utils\Traits\Listeners\HasNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderAcceptedNotification
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
    public function handle(OrderAccepted $event): void
    {
        $title = [
            'ar' => 'قام الفني بقبول طلبك',
            'en' => 'The technician has accepted your request',
            'hi' => 'तकनीशियन ने आपका अनुरोध स्वीकार कर लिया है',
            'bn' => 'প্রযুক্তিবিদ আপনার অনুরোধ গ্রহণ করেছেন',
            'ur' => 'فنی نے آپ کی درخواست قبول کر لی ہے',
            'tl' => 'Tinanggap ng technician ang iyong kahilingan',
            'id' => 'Teknisi telah menerima permintaan Anda',
            'fr' => 'Le technicien a accepté votre demande',
        ];

        $description = [
            'ar' => 'تم تأكيد الطلب، والفني في طريقه للوصول إليك الآن.',
            'en' => 'Your request has been confirmed, and the technician is on the way to you now.',
            'hi' => 'आपका अनुरोध पुष्टि हो गया है, और तकनीशियन अब आपकी ओर बढ़ रहा है।',
            'bn' => 'আপনার অনুরোধ নিশ্চিত হয়েছে, এবং প্রযুক্তিবিদ এখন আপনার দিকে আসছে।',
            'ur' => 'آپ کی درخواست کی تصدیق ہو گئی ہے، اور فنی اب آپ کی طرف آ رہا ہے۔',
            'tl' => 'Nakumpirma na ang iyong kahilingan, at ang technician ay papunta na sa iyo.',
            'id' => 'Permintaan Anda telah dikonfirmasi, dan teknisi sedang dalam perjalanan menuju Anda.',
            'fr' => 'Votre demande a été confirmée, et le technicien est en route vers vous.',
        ];

        $type = 'order-accepted';

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
