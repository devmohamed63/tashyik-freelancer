<?php
use App\Models\User;
use App\Models\Order;
use App\Models\PayoutRequest;
use App\Models\Subscription;
use App\Models\Contact;

User::factory(40)->create();

User::factory(40)->create([
    'type' => User::SERVICE_PROVIDER_ACCOUNT_TYPE,
]);

$customerIds = User::isUser()->pluck('id')->toArray();

foreach ($customerIds as $id) {
    Order::factory(8)->create([
        'customer_id' => $id,
        'service_provider_id' => null,
        'status' => Order::NEW_STATUS,
    ]);

    Order::factory(5)->create([
        'customer_id' => $id,
        'status' => Order::COMPLETED_STATUS,
        'service_provider_id' => 2002, // Or random
    ]);
}

$spIds = User::isServiceProvider()->pluck('id')->toArray();

foreach ($spIds as $spId) {
    $planId = \App\Models\Plan::inRandomOrder()->first()->id ?? 1;
    Subscription::factory(1)->create([
        'user_id' => $spId,
        'plan_id' => $planId,
    ]);
}

Contact::factory(30)->create();

echo "Seeded a lot of data!\n";
