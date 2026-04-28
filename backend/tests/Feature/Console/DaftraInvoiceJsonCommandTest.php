<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DaftraInvoiceJsonCommandTest extends TestCase
{
    public function test_shows_http_status_and_api_message_on_404(): void
    {
        config([
            'services.daftra.subdomain' => 'testcompany',
            'services.daftra.api_key' => 'test-api-key',
        ]);

        Http::preventStrayRequests();
        Http::fake(function (\Illuminate\Http\Client\Request $request) {
            if (! str_contains($request->url(), '/api2/invoices/42.json')) {
                return Http::response('unexpected URL in test', 599);
            }

            return Http::response([
                'result' => 'failed',
                'code' => 404,
                'message' => 'Invoice not found',
            ], 404);
        });

        $this->artisan('daftra:invoice-json', ['id' => 42])
            // Laravel matches each substring to a separate doWrite(); one line must be one assertion.
            ->expectsOutputToContain('HTTP 404 — Invoice not found')
            ->assertExitCode(1);
    }

    public function test_prints_json_and_resolved_public_link_on_success(): void
    {
        config([
            'services.daftra.subdomain' => 'testcompany',
            'services.daftra.api_key' => 'test-api-key',
        ]);

        $public = 'https://testcompany.daftra.com/client/invoice/abc123';

        Http::preventStrayRequests();
        Http::fake(function (\Illuminate\Http\Client\Request $request) use ($public) {
            if (! str_contains($request->url(), '/api2/invoices/7.json')) {
                return Http::response('unexpected URL in test', 599);
            }

            return Http::response([
                'data' => [
                    'Invoice' => [
                        'id' => 7,
                        'public_link' => $public,
                    ],
                ],
            ], 200);
        });

        $exit = Artisan::call('daftra:invoice-json', ['id' => 7, '--resolve' => true]);
        $this->assertSame(0, $exit);
        $out = Artisan::output();
        $this->assertStringContainsString('public_link', $out);
        $this->assertStringContainsString('https://testcompany.daftra.com/client/invoices/view/7', $out);
        $this->assertStringContainsString($public, $out);
    }
}
