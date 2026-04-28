<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Contracts\View\View;

class PublicInvoiceController extends Controller
{
    public function __invoke(Invoice $invoice): View
    {
        $invoice->loadMissing(['serviceProvider:id,name']);

        return view('public.invoice', [
            'invoice' => $invoice,
        ]);
    }
}
