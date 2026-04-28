<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use App\Mail\ContactMessage;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(ContactRequest $request)
    {
        $contact = Contact::create($request->validated());

        try {
            Mail::to('info@apptml.com')->send(new ContactMessage($contact));
        } catch (\Exception $e) {
            // Log or ignore failure to not break user experience
            \Illuminate\Support\Facades\Log::error('Failed to send contact email: ' . $e->getMessage());
        }

        return response()->noContent(201);
    }
}
