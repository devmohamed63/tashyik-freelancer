<?php

namespace Tests\Unit;

use App\Utils\Services\Daftra;
use Tests\TestCase;

class DaftraRecipientViewUrlTest extends TestCase
{
    public function test_skips_daftra_owner_urls_and_uses_public_link(): void
    {
        config([
            'services.daftra.api_key' => 'k',
            'services.daftra.subdomain' => 'acme',
        ]);

        $json = [
            'Invoice' => [
                'invoice_html_url' => 'https://acme.daftra.com/owner/invoices/view/55',
                'public_link' => 'https://acme.daftra.com/invoices/preview/55?hash=xyz',
            ],
        ];

        $daftra = app(Daftra::class);
        $resolved = $daftra->resolveRecipientViewUrlFromPayload($json);

        $this->assertSame('https://acme.daftra.com/invoices/preview/55?hash=xyz', $resolved);
    }
}
