<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$catIds = \App\Models\Category::isParent()->pluck('id')->toArray();
$users = \App\Models\User::isServiceProvider()->get();

foreach ($users as $user) {
    if ($user->categories()->count() === 0) {
        $user->categories()->attach((array) array_rand(array_flip($catIds), rand(1,3)));
        echo "Attached to user " . $user->id . "\n";
    }
}
echo "Done!\n";
