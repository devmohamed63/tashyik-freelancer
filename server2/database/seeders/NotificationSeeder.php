<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    private function generateRandomAmount(): array
    {
        $amount = rand(10, 200);

        return [
            'value' => (float) $amount,
            'formated' => number_format($amount, config('app.decimal_places')),
            'currency' => [
                'ar' => __('ui.currency', [], 'ar'),
                'en' => __('ui.currency', [], 'en')
            ]
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::find(2001);
        $serviceProviders = User::find(2002);

        // User notifications
        $user->notifications()
            ->createMany([
                [
                    'type' => 'order-accepted',
                    'title' => 'قام الفني بقبول طلبك',
                    'description' => 'تم تأكيد الطلب، والفني في طريقه للوصول إليك الآن.',
                    'data' => json_encode([
                        'order' => [
                            'id' => 5,
                        ],
                    ]),
                ],
                [
                    'type' => 'service-provider-on-the-way',
                    'title' => 'الفني في طريقه اليك',
                    'description' => 'تم تأكيد الطلب، والفني في طريقه للوصول إليك الآن.',
                    'data' => json_encode([
                        'order' => [
                            'id' => 8,
                        ],
                    ]),
                ],
                [
                    'type' => 'service-provider-arrived',
                    'title' => 'وصل الفني لموقع الخدمة',
                    'description' => 'الفني وصل الموقع المحدد, وجاهز لبدء تنفيذ الخدمة.',
                    'data' => json_encode([
                        'order' => [
                            'id' => 6,
                        ],
                    ]),
                ],
                [
                    'type' => 'order-started',
                    'title' => 'بدء الفني في تنفيذ طلبك',
                    'description' => 'يعمل الفني المختص حالياً على تنفيذ خدمتك، وسيتم إشعارك فور الانتهاء.',
                    'data' => json_encode([
                        'order' => [
                            'id' => 3,
                        ],
                    ]),
                ],
                [
                    'type' => 'order-completed',
                    'title' => 'انهى الفني تنفيذ طلبك بنجاح',
                    'description' => 'نأمل أن تكون الخدمة قد نالت رضاك. لا تنسَ مشاركتنا تقييمك.',
                    'data' => json_encode([
                        'order' => [
                            'id' => 12,
                        ],
                    ]),
                ],
                [
                    'type' => 'new-order-extra',
                    'title' => 'طلب دفع للخدمات الإضافية',
                    'description' => 'أصدر الفني فاتورة بالخدمات الإضافية، يُرجى السداد لاستكمال الطلب.',
                    'data' => json_encode([
                        'order' => [
                            'id' => 5,
                        ],
                    ]),
                ],
            ]);

        $serviceProviders->notifications()
            ->createMany([
                [
                    'type' => 'account-approved',
                    'title' => 'تم الموافقة علي حسابك',
                    'description' => 'يمكنك الآن البدء في استخدام التطبيق وتلقي الطلبات.',
                ],
                [
                    'type' => 'new-order',
                    'title' => 'يوجد طلب جديد',
                    'description' => 'لديك طلب جديد، يمكنك الاطلاع على التفاصيل والبدء في التنفيذ.',
                    'data' => json_encode([
                        'order' => [
                            'id' => 5,
                        ],
                    ]),
                ],
                [
                    'type' => 'order-extra-paid',
                    'title' => 'دفع العميل تكلفة الخدمات الإضافية',
                    'description' => 'دفع العميل رسوم الخدمات الإضافية, طلبك جاهز للمتابعة.',
                    'data' => json_encode([
                        'order' => [
                            'id' => 5,
                        ],
                    ]),
                ],
                [
                    'type' => 'bank-transfer',
                    'title' => 'تم عمل تحويل بنكي لحسابك.',
                    'description' => 'تم الموافقة على طلبك لسحب الرصيد وتم تحويل المبلغ إلى حسابك البنكي.',
                ],
            ]);
    }
}
