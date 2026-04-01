<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $descriptions = [
            'عندمـا يتعـلق الأمـر بالحفـاظ علـى برودة مـنزلك وكفـاءة التكـييف, فإن خدمتنا ستلبي احتياجك تمامًا. احجز فني متخصص عبر تشيك واستمتع بأداء مثالي لمكيفك طوال العام.',
            'تسربات المياه والأعطال المفاجئة قد تسبب إزعاجًا كبيرًا. احجز فني سباكة محترف عبر تشيك لضمان إصلاح دقيق وسريع يحافظ على منزلك آمنًا وجافًا.',
            'سلامة التمديدات والأعطال الكهربائية تحتاج إلى خبرة موثوقة. من خلال تشيك يمكنك طلب كهربائي متخصص ينجز العمل بكفاءة وأمان تام.',
            'سواءً لإصلاح الأبواب أو تركيب قطع جديدة، نقدم لك خدمة نجارة احترافية تهتم بأدق التفاصيل. احجز عبر تشيك واستمتع بلمسة متقنة في منزلك.',
            'جدّد مظهر منزلك بألوان متناسقة وتشطيب مثالي. عبر تشيك نوفر لك فني دهانات يضمن نتيجة أنيقة تضيف حيوية لكل مساحة.',
            'تجنب الأضرار الخفية وارتفاع فواتير المياه مع خدمة كشف احترافية تستخدم أحدث التقنيات. احجز عبر تشيك لحلول سريعة ودقيقة.',
            'حافظ على بيئة صحية وآمنة لعائلتك. عبر تشيك يمكنك طلب خدمة مكافحة فعالة بمواد آمنة ونتائج تدوم طويلًا.',
        ];

        return [
            'name' =>  fake()->words(asText: true),
            'description' => fake()->randomElement($descriptions),
        ];
    }

    /**
     * Indicate that the model should be parent.
     */
    public function parent(): static
    {
        return $this->state(fn(array $attributes) => [
            'category_id' => null,
        ]);
    }
}
