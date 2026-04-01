<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Highlight>
 */
class HighlightFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            "إصلاح تسريبات المياه وتركيب وصيانة الخلاطات والأحواض.",
            "إصلاح الأعطال الكهربائية وتركيب المفاتيح ووحدات الإضاءة.",
            "تصليح المفصلات والأقفال وضبط إغلاق الأبواب.",
            "إصلاح الشبابيك وضبط الألمنيوم ومنع تسرب الهواء.",
            "تركيب وصيانة سخانات المياه بجميع أنواعها.",
            "فحص وكشف تسربات المياه بأجهزة حديثة بدون تكسير.",
            "معالجة الرطوبة والتشققات وإعادة عزل الأسقف.",
            "تركيب وحدات الإضاءة والنجف بشكل آمن.",
            "تنظيف وتعقيم خزانات المياه وضمان سلامتها.",
            "تركيب الستائر وتثبيتها بدقة وثبات."
        ];

        return [
            'title' => fake()->randomElement($titles),
        ];
    }
}
