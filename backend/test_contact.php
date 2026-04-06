<?php

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ContactController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$request = Request::create('/api/contact-requests', 'POST', [
    'name' => 'John Doe Test',
    'email' => 'john.test@example.com',
    'phone' => '+966500000000',
    'subject' => 'test contact form via script',
    'message' => 'Hello this is an automated test to see if the contact email arrives at mohmahmoudd63@gmail.com!'
]);

// Since the request has validation, we need to bind the request or call the controller directly.
try {
    $contact = Contact::create($request->all());
    \Illuminate\Support\Facades\Mail::to('mohamed202203785@gmail.com')->send(new \App\Mail\ContactMessage($contact));
    echo "Success! The contact was saved and email has been triggered via script.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
