<?php

namespace App\Listeners;

use App\Events\ServiceProviderApproved;
use App\Utils\Traits\Listeners\HasNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendApprovalNotificationToServiceProvider
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
    public function handle(ServiceProviderApproved $event): void
    {
        $title =  [
            'ar' => 'تم الموافقة علي حسابك',
            'en' => 'Your account has been approved',
            'hi' => 'आपका खाता स्वीकृत हो गया है',
            'bn' => 'আপনার অ্যাকাউন্ট অনুমোদিত হয়েছে',
            'ur' => 'آپ کا اکاؤنٹ منظور کر دیا گیا ہے',
            'tl' => 'Naaprubahan na ang iyong account',
            'id' => 'Akun Anda telah disetujui',
            'fr' => 'Votre compte a été approuvé',
        ];

        $description = [
            'ar' => 'يمكنك الآن البدء في استخدام التطبيق وتلقي الطلبات.',
            'en' => 'You can now start using the app and receive requests.',
            'hi' => 'अब आप ऐप का उपयोग करना शुरू कर सकते हैं और अनुरोध प्राप्त कर सकते हैं।',
            'bn' => 'এখন আপনি অ্যাপ ব্যবহার শুরু করতে পারেন এবং অনুরোধ গ্রহণ করতে পারেন।',
            'ur' => 'اب آپ ایپ استعمال کرنا شروع کر سکتے ہیں اور درخواستیں وصول کر سکتے ہیں۔',
            'tl' => 'Maaari mo nang simulan ang paggamit ng app at tumanggap ng mga request.',
            'id' => 'Anda sekarang dapat mulai menggunakan aplikasi dan menerima permintaan.',
            'fr' => 'Vous pouvez maintenant commencer à utiliser l’application et recevoir des demandes.',
        ];

        $type = 'account-approved';

        $this->sendNotification(
            type: $type,
            title: $title,
            description: $description,
            pushNotification: true,
            recipient: $event->serviceProvider,
        );
    }
}
