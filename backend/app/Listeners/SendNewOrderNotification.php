<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\NewOrder;
use App\Utils\Traits\Listeners\HasNotifications;
use App\Utils\Services\Firebase\Firestore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNewOrderNotification
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
    public function handle(NewOrder $event): void
    {
        $title = [
            'ar' => 'يوجد طلب جديد',
            'en' => 'You have a new request',
            'hi' => 'आपके पास एक नया अनुरोध है',
            'bn' => 'আপনার কাছে একটি নতুন অনুরোধ আছে',
            'ur' => 'آپ کے پاس ایک نیا درخواست ہے',
            'tl' => 'May bagong kahilingan ka',
            'id' => 'Anda memiliki permintaan baru',
            'fr' => 'Vous avez une nouvelle demande',
        ];

        $description = [
            'ar' => 'لديك طلب جديد، يمكنك الاطلاع على التفاصيل والبدء في التنفيذ.',
            'en' => 'You have a new request, check the details and start processing it.',
            'hi' => 'आपके पास एक नया अनुरोध है, विवरण देखें और इसे पूरा करना शुरू करें।',
            'bn' => 'আপনার কাছে একটি নতুন অনুরোধ আছে, বিস্তারিত দেখুন এবং এটি সম্পন্ন করতে শুরু করুন।',
            'ur' => 'آپ کے پاس ایک نیا درخواست ہے، تفصیلات دیکھیں اور عمل شروع کریں۔',
            'tl' => 'May bagong kahilingan ka, tingnan ang mga detalye at simulan ang pagsasagawa.',
            'id' => 'Anda memiliki permintaan baru, periksa detailnya dan mulai prosesnya.',
            'fr' => 'Vous avez une nouvelle demande, consultez les détails et commencez le traitement.',
        ];

        $type = 'new-order';

        $order = $event->order;

        if ($order->latitude && $order->longitude) {
            $serviceProviders = User::isServiceProvider()
                ->select([
                    'id',
                    'ui_locale',
                    'fcm_token',
                    'latitude',
                    'longitude',
                ])
                ->whereHas('categories', function (Builder $query) use ($order) {
                    $query->where('id', $order->category_id);
                })
                ->withinMaxDistance($order->latitude, $order->longitude)
                ->get();

            foreach ($serviceProviders as $serviceProvider) {
                $this->sendNotification(
                    type: $type,
                    title: $title,
                    description: $description,
                    pushNotification: true,
                    recipient: $serviceProvider,
                );
            }
        }

        if (!app()->isProduction()) return;

        // Update firestore analytics
        $firestore = new Firestore();
        $firestore->incrementFields([
            [
                'category_analytics/' . $order->category_id,
                'count',
            ],
            [
                'city_analytics/' . $order->customer->city_id,
                'count',
            ],
        ]);
    }
}
