<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\City;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;

$cities = City::pluck('id')->toArray();
$categories = Category::isParent()->pluck('id')->toArray();

$companies = [
    'شركة النخبة للخدمات',
    'شركة الأمان للصيانة',
    'شركة الإتقان المتحدة',
    'شركة البناء الذهبي',
    'شركة الرواد للحلول',
];

$employees = [
    ['سعيد أحمد العتيبي', '0512340001'],
    ['خالد محمد الشمري', '0512340002'],
    ['فهد عبدالله القحطاني', '0512340003'],
    ['ياسر إبراهيم الدوسري', '0512340004'],
    ['عمر حسن المالكي', '0512340005'],
    ['نايف سعد الحربي', '0512340006'],
    ['بدر فيصل الغامدي', '0512340007'],
    ['تركي ناصر الزهراني', '0512340008'],
    ['ماجد علي السبيعي', '0512340009'],
    ['راشد عبدالرحمن المطيري', '0512340010'],
    ['حمد سلطان العنزي', '0512340011'],
    ['مشاري وليد الرشيدي', '0512340012'],
    ['عبدالعزيز خالد البلوي', '0512340013'],
    ['سلمان يوسف الجهني', '0512340014'],
    ['وائل طارق الثبيتي', '0512340015'],
];

$empIndex = 0;

foreach ($companies as $i => $companyName) {
    // Create company
    $company = new User();
    $company->name = $companyName;
    $company->phone = '055100' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
    $company->email = 'company' . ($i + 1) . '@test.com';
    $company->password = Hash::make('12345678');
    $company->type = User::SERVICE_PROVIDER_ACCOUNT_TYPE;
    $company->entity_type = User::COMPANY_ENTITY_TYPE;
    $company->status = User::ACTIVE_STATUS;
    $company->city_id = $cities[array_rand($cities)];
    $company->residence_name = $companyName;
    $company->residence_number = '110' . ($i + 1);
    $company->bank_name = 'الراجحي';
    $company->iban = 'SA' . str_pad(rand(1000000000, 9999999999), 22, '0', STR_PAD_LEFT);
    $company->commercial_registration_number = '100' . str_pad($i + 1, 7, '0', STR_PAD_LEFT);
    $company->save();

    if (!empty($categories)) {
        $company->categories()->attach(array_slice($categories, 0, min(2, count($categories))));
    }

    echo "Created company: {$companyName} (ID: {$company->id})\n";

    // Add 3 employees per company
    $memberCount = 3;
    for ($j = 0; $j < $memberCount && $empIndex < count($employees); $j++, $empIndex++) {
        $emp = new User();
        $emp->name = $employees[$empIndex][0];
        $emp->phone = $employees[$empIndex][1];
        $emp->password = Hash::make('12345678');
        $emp->type = User::SERVICE_PROVIDER_ACCOUNT_TYPE;
        $emp->entity_type = User::INDIVIDUAL_ENTITY_TYPE;
        $emp->status = User::ACTIVE_STATUS;
        $emp->institution_id = $company->id;
        $emp->city_id = $cities[array_rand($cities)];
        $emp->residence_name = $employees[$empIndex][0];
        $emp->residence_number = '220' . str_pad($empIndex + 1, 4, '0', STR_PAD_LEFT);
        $emp->bank_name = 'الراجحي';
        $emp->iban = 'SA' . str_pad(rand(1000000000, 9999999999), 22, '0', STR_PAD_LEFT);
        $emp->save();

        if (!empty($categories)) {
            $emp->categories()->attach(array_slice($categories, 0, min(2, count($categories))));
        }

        echo "  -> Employee: {$emp->name} (ID: {$emp->id})\n";
    }
}

echo "\nDone! Created 5 companies with 15 employees total.\n";
