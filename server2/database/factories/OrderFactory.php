<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $descriptions = [
            '', # Empty description
            "يوجد تشققات واضحة في سقف الصالة مع تساقط بسيط في الطلاء، خاصة بعد الأمطار الأخيرة.",
            "الغسالة تتوقف في منتصف دورة الغسيل وتظهر إشارة خطأ، مع بقاء المياه داخل الحوض.",
            "مروحة السقف تدور ببطء حتى عند اختيار السرعة العالية، مع وجود اهتزاز خفيف أثناء التشغيل.",
            "المرحاض يستمر في تصريف المياه بعد الاستخدام لفترة طويلة، مما يؤدي إلى ارتفاع فاتورة المياه.",
            "شفاط المطبخ لا يسحب الروائح بشكل فعال، ويصدر صوتاً أعلى من المعتاد.",
            "بلاط المطبخ مفكوك في أكثر من مكان ويصدر صوت فراغ عند المشي عليه.",
        ];

        return [
            'category_id' => Category::inRandomOrder()->first('id')?->id,
            'service_id' => Service::inRandomOrder()->first('id')?->id,
            'address_id' => Address::inRandomOrder()->first('id')?->id,
            'service_provider_id' => User::notUser()->whereNot('id', 2002)->inRandomOrder()->first('id')?->id,
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'description' => fake()->randomElement($descriptions),
            'quantity' => fake()->numberBetween(1, 3),
            'visit_cost' => fake()->numberBetween(0, 50),
            'subtotal' => fake()->numberBetween(50, 200),
            'tax_rate' => fake()->numberBetween(5, 20),
            'tax' => fake()->numberBetween(10, 50),
            'coupons_total' => fake()->numberBetween(0, 200),
            'wallet_balance' => fake()->numberBetween(0, 200),
            'total' => fake()->numberBetween(100, 300),
            'status' => fake()->randomElement(Order::AVAILABLE_STATUS_TYPES),
            'service_provider_notes' => fake()->paragraph(),
        ];
    }
}
