<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Utils\Traits\Listeners\HasNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderCompletedNotification
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
    public function handle(OrderCompleted $event): void
    {
        $title = [
            'ar' => 'انهى الفني تنفيذ طلبك بنجاح',
            'en' => 'The technician has successfully completed your request',
            'hi' => 'तकनीशियन ने आपका अनुरोध सफलतापूर्वक पूरा कर दिया है',
            'bn' => 'প্রযুক্তিবিদ আপনার অনুরোধ সফলভাবে সম্পন্ন করেছেন',
            'ur' => 'فنی نے آپ کی درخواست کامیابی کے ساتھ مکمل کر دی ہے',
            'tl' => 'Matagumpay na natapos ng technician ang iyong kahilingan',
            'id' => 'Teknisi telah berhasil menyelesaikan permintaan Anda',
            'fr' => 'Le technicien a terminé votre demande avec succès',
        ];

        $description = [
            'ar' => 'نأمل أن تكون الخدمة قد نالت رضاك. لا تنسَ مشاركتنا تقييمك.',
            'en' => 'We hope you are satisfied with the service. Don’t forget to share your feedback.',
            'hi' => 'हमें उम्मीद है कि आप सेवा से संतुष्ट हैं। अपनी प्रतिक्रिया साझा करना न भूलें।',
            'bn' => 'আমরা আশা করি আপনি পরিষেবায় সন্তুষ্ট। আপনার প্রতিক্রিয়া শেয়ার করতে ভুলবেন না।',
            'ur' => 'ہمیں امید ہے کہ آپ سروس سے مطمئن ہیں۔ اپنی رائے کا اشتراک کرنا نہ بھولیں۔',
            'tl' => 'Inaasahan naming nasiyahan ka sa serbisyo. Huwag kalimutan ibahagi ang iyong feedback.',
            'id' => 'Kami harap Anda puas dengan layanan ini. Jangan lupa bagikan umpan balik Anda.',
            'fr' => 'Nous espérons que le service vous a satisfait. N’oubliez pas de partager votre avis.',
        ];

        $type = 'order-completed';

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
