<?php

namespace Tests\Unit;

use App\Utils\Services\Daftra;
use Tests\TestCase;

class DaftraClientInvoiceViewUrlTest extends TestCase
{
    public function test_client_invoice_view_url_uses_subdomain_and_path(): void
    {
        config(['services.daftra.subdomain' => 'acme']);

        $daftra = app(Daftra::class);

        $this->assertSame(
            'https://acme.daftra.com/client/invoices/view/18',
            $daftra->clientInvoiceViewUrl(18)
        );
    }
}
