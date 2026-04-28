<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Contracts\View\View;

class PublicInvoiceByTokenController extends Controller
{
    public function __invoke(string $view_token): View
    {
        $invoice = Invoice::query()->where('view_token', $view_token)->firstOrFail();
        $invoice->loadMissing(['serviceProvider:id,name']);

        return view('public.invoice', [
            'invoice' => $invoice,
        ]);
    }
}
