<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => [
                    'ar' => '🛠️ باقة البداية',
                    'en' => '🛠️ Starter Package',
                    'hi' => '🛠️ शुरुआती पैकेज',
                    'bn' => '🛠️ স্টার্টার প্যাকেজ',
                    'ur' => '🛠️ اسٹارٹر پیکیج',
                    'tl' => '🛠️ Starter Package',
                    'id' => '🛠️ Paket Pemula',
                    'fr' => '🛠️ Forfait Débutant',
                ],
                'price' => 300,
                'target_group' => User::INDIVIDUAL_ENTITY_TYPE,
                'duration_in_days' => 30,
            ],
            [
                'name' => [
                    'ar' => '✨ باقة الاحترافية',
                    'en' => '✨ Professional Package',
                    'hi' => '✨ प्रोफेशनल पैकेज',
                    'bn' => '✨ প্রফেশনাল প্যাকেজ',
                    'ur' => '✨ پروفیشنل پیکیج',
                    'tl' => '✨ Professional Package',
                    'id' => '✨ Paket Profesional',
                    'fr' => '✨ Forfait Professionnel',
                ],
                'price' => 600,
                'target_group' => User::INDIVIDUAL_ENTITY_TYPE,
                'duration_in_days' => 60,
            ],
            [
                'name' => [
                    'ar' => '🏆 باقة الريادة',
                    'en' => '🏆 Leadership Package',
                    'hi' => '🏆 लीडरशिप पैकेज',
                    'bn' => '🏆 লিডারশিপ প্যাকেজ',
                    'ur' => '🏆 لیڈرشپ پیکیج',
                    'tl' => '🏆 Leadership Package',
                    'id' => '🏆 Paket Kepemimpinan',
                    'fr' => '🏆 Forfait Leadership',
                ],
                'price' => 1200,
                'target_group' => User::INSTITUTION_ENTITY_TYPE,
                'duration_in_days' => 90,
            ],
            [
                'name' => [
                    'ar' => '⚡ باقة النخبة',
                    'en' => '⚡ Elite Package',
                    'hi' => '⚡ एलीट पैकेज',
                    'bn' => '⚡ এলিট প্যাকেজ',
                    'ur' => '⚡ ایلیٹ پیکیج',
                    'tl' => '⚡ Elite Package',
                    'id' => '⚡ Paket Elite',
                    'fr' => '⚡ Forfait Élite',
                ],
                'price' => 1700,
                'target_group' => User::INSTITUTION_ENTITY_TYPE,
                'duration_in_days' => 90,
            ],
            [
                'name' => [
                    'ar' => '👑 الباقة الذهبية',
                    'en' => '👑 Gold Package',
                    'hi' => '👑 गोल्ड पैकेज',
                    'bn' => '👑 গোল্ড প্যাকেজ',
                    'ur' => '👑 گولڈ پیکیج',
                    'tl' => '👑 Gold Package',
                    'id' => '👑 Paket Gold',
                    'fr' => '👑 Forfait Or',
                ],
                'price' => 2000,
                'target_group' => User::COMPANY_ENTITY_TYPE,
                'duration_in_days' => 30,
            ],
            [
                'name' => [
                    'ar' => '🔖 باقة الشامل',
                    'en' => '🔖 Comprehensive Package',
                    'hi' => '🔖 व्यापक पैकेज',
                    'bn' => '🔖 সম্পূর্ণ প্যাকেজ',
                    'ur' => '🔖 مکمل پیکیج',
                    'tl' => '🔖 Comprehensive Package',
                    'id' => '🔖 Paket Lengkap',
                    'fr' => '🔖 Forfait Complet',
                ],
                'price' => 2300,
                'target_group' => User::COMPANY_ENTITY_TYPE,
                'duration_in_days' => 90,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create([
                'name' => $plan['name'],
                'price' => $plan['price'],
                'target_group' => $plan['target_group'],
                'duration_in_days' => $plan['duration_in_days'],
            ]);
        }
    }
}
