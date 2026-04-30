<?php

namespace App\Listeners;

use App\Events\OrderStarted;
use App\Utils\Traits\Listeners\HasNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderStartedNotification
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
    public function handle(OrderStarted $event): void
    {
        $title = [
            'ar' => 'بدء الفني في تنفيذ طلبك',
            'en' => 'The technician has started working on your request',
            'hi' => 'तकनीशियन ने आपके अनुरोध पर काम शुरू कर दिया है',
            'bn' => 'প্রযুক্তিবিদ আপনার অনুরোধ সম্পাদন শুরু করেছেন',
            'ur' => 'فنی نے آپ کی درخواست پر کام شروع کر دیا ہے',
            'tl' => 'Sinimulan na ng technician ang pagtatrabaho sa iyong kahilingan',
            'id' => 'Teknisi telah memulai pekerjaan pada permintaan Anda',
            'fr' => 'Le technicien a commencé à traiter votre demande',
        ];

        $description = [
            'ar' => 'يعمل الفني المختص حالياً على تنفيذ خدمتك، وسيتم إشعارك فور الانتهاء.',
            'en' => 'The assigned technician is currently working on your service and you will be notified once it is completed.',
            'hi' => 'नियुक्त तकनीशियन वर्तमान में आपकी सेवा पर काम कर रहा है और पूरा होने पर आपको सूचित किया जाएगा।',
            'bn' => 'নির্ধারিত প্রযুক্তিবিদ বর্তমানে আপনার সেবায় কাজ করছেন, এবং শেষ হলে আপনাকে জানানো হবে।',
            'ur' => 'متعین فنی اس وقت آپ کی سروس پر کام کر رہا ہے اور مکمل ہونے پر آپ کو مطلع کیا جائے گا۔',
            'tl' => 'Ang nakatalagang technician ay kasalukuyang nagtatrabaho sa iyong serbisyo at ipapaalam sa iyo kapag natapos na ito.',
            'id' => 'Teknisi yang ditugaskan sedang mengerjakan layanan Anda dan Anda akan diberitahu setelah selesai.',
            'fr' => 'Le technicien assigné travaille actuellement sur votre service et vous serez notifié une fois terminé.',
        ];

        $type = 'order-started';

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
