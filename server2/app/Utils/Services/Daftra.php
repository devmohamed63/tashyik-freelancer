<?php

namespace App\Utils\Services;

use App\Models\User;
use App\Utils\Services\Daftra\DTOs\CreditNoteDTO;
use App\Utils\Services\Daftra\DTOs\ExpenseDTO;
use App\Utils\Services\Daftra\DTOs\InvoiceDTO;
use App\Utils\Services\Daftra\DTOs\PaymentDTO;
use App\Utils\Services\Daftra\DTOs\ProductDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Daftra
{
    private string $baseUrl;

    private ?string $apiKey;

    public function __construct()
    {
        $subdomain = config('services.daftra.subdomain');
        $this->apiKey = config('services.daftra.api_key');
        $this->baseUrl = "https://{$subdomain}.daftra.com/api2";
    }

    /**
     * Get Daftra configuration value via helper.
     */
    public function getConfig(string $key): mixed
    {
        return config("services.daftra.{$key}");
    }

    /**
     * Owner web UI base URL (not API). Uses DAFTRA_SUBDOMAIN from config.
     */
    public function ownerPortalBaseUrl(): string
    {
        $subdomain = (string) $this->getConfig('subdomain');

        return "https://{$subdomain}.daftra.com";
    }

    /**
     * Sales invoices list in Daftra (same area as المبيعات → إدارة الفواتير).
     */
    public function ownerInvoicesIndexUrl(): string
    {
        return $this->ownerPortalBaseUrl().'/owner/invoices/index';
    }

    /**
     * Owner-console URL (staff). Opens Daftra login if the browser session is not authenticated.
     * For service-provider / recipient links use {@see clientInvoiceViewUrl()} or {@see \App\Models\Invoice::invoiceEmailPrimaryUrl()}.
     */
    public function ownerInvoiceViewUrl(int $daftraInvoiceId): string
    {
        return $this->ownerPortalBaseUrl().'/owner/invoices/view/'.$daftraInvoiceId;
    }

    /**
     * Client-area invoice view (no staff /owner/ path). Same host as {@see ownerPortalBaseUrl()}.
     * Used in emails as the canonical recipient link when {@see \App\Models\Invoice::$daftra_id} is set.
     */
    public function clientInvoiceViewUrl(int $daftraInvoiceId): string
    {
        return rtrim($this->ownerPortalBaseUrl(), '/').'/client/invoices/view/'.$daftraInvoiceId;
    }

    /**
     * Tokenized client-facing invoice URL from Daftra API (HTML preview preferred, else PDF).
     * For persisted diagnostics see {@see \App\Models\Invoice::$daftra_public_view_url}; for email CTAs prefer {@see clientInvoiceViewUrl()}.
     */
    public function fetchSalesInvoiceRecipientViewUrl(int $daftraInvoiceId): ?string
    {
        if (! (bool) config('services.daftra.fetch_public_invoice_url', true)) {
            return null;
        }

        $json = $this->getSalesInvoiceJson($daftraInvoiceId);

        return $this->resolveRecipientViewUrlFromPayload($json);
    }

    /**
     * Pick customer-facing URL or PDF link from a GET invoices/{id}.json body (no extra HTTP).
     */
    public function resolveRecipientViewUrlFromPayload(?array $json): ?string
    {
        if (! is_array($json)) {
            return null;
        }

        $browser = $this->extractInvoicePublicBrowserUrlFromPayload($json);
        if (is_string($browser) && filter_var($browser, FILTER_VALIDATE_URL)) {
            return $browser;
        }

        return $this->extractInvoicePdfUrlFromPayload($json);
    }

    /**
     * invoice_pdf_url (and aliases) from GET invoices/{id}.json body (no extra HTTP).
     */
    public function resolveSalesInvoicePdfUrlFromPayload(?array $json): ?string
    {
        return is_array($json) ? $this->extractInvoicePdfUrlFromPayload($json) : null;
    }

    /**
     * Raw GET invoices/{id}.json payload (for diagnostics: php artisan daftra:invoice-json).
     */
    public function getSalesInvoiceRecord(int $daftraInvoiceId): ?array
    {
        return $this->getSalesInvoiceJson($daftraInvoiceId);
    }

    /**
     * Same GET as {@see getSalesInvoiceRecord()} but always returns HTTP metadata (CLI / scripts).
     *
     * @return array{
     *     configured: bool,
     *     request_url: string,
     *     http_ok: bool,
     *     http_status: int,
     *     api_message: ?string,
     *     payload: ?array
     * }
     */
    public function getSalesInvoiceFetchDiagnostics(int $daftraInvoiceId): array
    {
        $endpoint = "invoices/{$daftraInvoiceId}.json";
        $requestUrl = "{$this->baseUrl}/{$endpoint}";

        if (! $this->apiKey) {
            return [
                'configured' => false,
                'request_url' => $requestUrl,
                'http_ok' => false,
                'http_status' => 0,
                'api_message' => 'DAFTRA_API_KEY غير مضبوط.',
                'payload' => null,
            ];
        }

        $response = $this->salesInvoiceHttpGet($daftraInvoiceId);
        $decoded = $response->json();
        $payload = is_array($decoded) ? $decoded : null;
        $apiMessage = $this->extractDaftraJsonMessage($payload) ?? (! $response->successful() ? mb_substr($response->body(), 0, 500) : null);

        return [
            'configured' => true,
            'request_url' => $requestUrl,
            'http_ok' => $response->successful(),
            'http_status' => $response->status(),
            'api_message' => is_string($apiMessage) && $apiMessage !== '' ? $apiMessage : null,
            'payload' => $response->successful() ? $payload : null,
        ];
    }

    /**
     * Make an authenticated request to the Daftra API with Built-in Retry (Queue Resilience).
     */
    private function request(string $method, string $endpoint, array $data = [])
    {
        // Fail loud in production so missing credentials surface immediately
        // via the queue's failed_jobs table instead of silently succeeding.
        if (! $this->apiKey) {
            if (app()->environment('production')) {
                throw new \RuntimeException(
                    'Daftra integration is not configured: DAFTRA_API_KEY is missing.'
                );
            }

            return null;
        }

        // Retry only on connection errors / 5xx; NEVER throw on client errors
        // so callers receive `null` and can log+continue without crashing the
        // job or the HTTP request that dispatched it.
        $response = Http::withHeaders([
            'APIKEY' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])
            ->retry(
                times: 3,
                sleepMilliseconds: 2000,
                when: fn ($exception) => $exception instanceof \Illuminate\Http\Client\ConnectionException,
                throw: false,
            )
            ->{$method}("{$this->baseUrl}/{$endpoint}", $data);

        if ($response->failed()) {
            Log::error("Daftra API error [{$method} {$endpoint}]", [
                'status' => $response->status(),
                'body' => $response->body(),
                'data' => $data,
            ]);

            return null;
        }

        return $response->json();
    }

    /**
     * Create or Sync a client in Daftra from a User model.
     *
     * Wrapped in an atomic lock per-user so that two concurrent jobs for the
     * same customer (e.g. two orders placed simultaneously) cannot both call
     * the Daftra API and create duplicate clients.
     */
    public function syncClient(User $user): ?int
    {
        if ($user->hasDaftraId()) {
            return $user->daftra_id;
        }

        return Cache::lock("daftra:sync-client:{$user->id}", 10)
            ->block(5, function () use ($user) {
                // Re-check inside the lock to avoid duplicate creates
                $user->refresh();
                if ($user->hasDaftraId()) {
                    return $user->daftra_id;
                }

                $typeId = $user->isInstitutionOrCompany() ? 2 : 1;

                // Daftra rejects requests with empty email/phone; provide
                // deterministic fallbacks so the sync cannot silently fail
                // for users registered via OTP only.
                $email = $user->email ?: "user-{$user->id}@noemail.tashyik.com";
                $phone = $user->phone ?: '-';
                $name = $user->name ?: "User #{$user->id}";

                $response = $this->request('post', 'clients', [
                    'Client' => [
                        'first_name' => $name,
                        'email' => $email,
                        'phone1' => $phone,
                        'type' => $typeId,
                        'notes' => "Synced from Tashyik | User #{$user->id}",
                    ],
                ]);

                $daftraId = $response['id']
                    ?? $response['Client']['id']
                    ?? null;

                if (! $daftraId) {
                    return null;
                }

                $user->setDaftraId($daftraId);
                Log::info("Daftra: Created client #{$daftraId} for user #{$user->id}");

                return $daftraId;
            });
    }

    /**
     * Create a structured invoice in Daftra.
     */
    public function createInvoice(InvoiceDTO $dto): ?int
    {
        $response = $this->request('post', 'invoices', $dto->toArray());

        if ($response && isset($response['id'])) {
            return $response['id'];
        } elseif ($response && isset($response['Invoice']['id'])) {
            return $response['Invoice']['id'];
        }

        return null;
    }

    /**
     * Create a structured product/service in Daftra.
     */
    public function createProduct(ProductDTO $dto): ?int
    {
        $response = $this->request('post', 'products', $dto->toArray());

        if ($response && isset($response['id'])) {
            return $response['id'];
        } elseif ($response && isset($response['Product']['id'])) {
            return $response['Product']['id'];
        }

        return null;
    }

    /**
     * Create a payment/receipt for an invoice (Bank integration).
     */
    public function createPayment(PaymentDTO $dto): ?int
    {
        $response = $this->request('post', 'client_payments', $dto->toArray());

        if ($response && isset($response['id'])) {
            return $response['id'];
        } elseif ($response && isset($response['ClientPayment']['id'])) {
            return $response['ClientPayment']['id'];
        }

        return null;
    }

    /**
     * Create a credit note in Daftra (مردود مبيعات / إشعار دائن).
     */
    public function createCreditNote(CreditNoteDTO $dto): ?int
    {
        $response = $this->request('post', 'credit_notes', $dto->toArray());

        if ($response && isset($response['id'])) {
            return $response['id'];
        } elseif ($response && isset($response['CreditNote']['id'])) {
            return $response['CreditNote']['id'];
        }

        return null;
    }

    /**
     * Create an expense entry (used for service-provider payout cash-outs).
     */
    public function createExpense(ExpenseDTO $dto): ?int
    {
        // Daftra docs naming can differ between accounts (expenses/expens),
        // so we attempt the canonical endpoint then the legacy alias.
        $response = $this->request('post', 'expenses', $dto->toArray())
            ?? $this->request('post', 'expens', $dto->toArray());

        if ($response && isset($response['id'])) {
            return (int) $response['id'];
        } elseif ($response && isset($response['Expense']['id'])) {
            return (int) $response['Expense']['id'];
        }

        return null;
    }

    /**
     * Resolve invoice_pdf_url from Daftra GET invoice (api2).
     *
     * @see https://docs.daftara.dev/
     */
    public function fetchSalesInvoicePdfUrl(int $daftraInvoiceId): ?string
    {
        $json = $this->getSalesInvoiceJson($daftraInvoiceId);
        if (! is_array($json)) {
            return null;
        }

        return $this->extractInvoicePdfUrlFromPayload($json);
    }

    /**
     * Download the sales invoice PDF bytes from Daftra (invoice_pdf_url + GET).
     */
    public function fetchSalesInvoicePdfBinary(int $daftraInvoiceId): ?string
    {
        $url = $this->fetchSalesInvoicePdfUrl($daftraInvoiceId);
        if (! is_string($url) || $url === '') {
            return null;
        }

        return $this->downloadPublicFile($url);
    }

    /**
     * Download a public URL (e.g. Daftra invoice_pdf_url with hash).
     *
     * Anonymous GET on daftra.com often returns an HTML sign-in page. When the host is daftra.com
     * and APIKEY is set, try that header first (same as api2), then fall back to a plain GET.
     */
    public function downloadPublicFile(string $url): ?string
    {
        try {
            $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?? ''));
            $tryApiKeyFirst = $this->apiKey && str_contains($host, 'daftra.com');

            $get = function (bool $withApiKey) use ($url): ?string {
                $pending = Http::timeout(90)
                    ->connectTimeout(15)
                    ->retry(2, 500, throw: false);

                if ($withApiKey && $this->apiKey) {
                    $pending = $pending->withHeaders(['APIKEY' => $this->apiKey]);
                }

                $response = $pending->get($url);
                if ($response->failed()) {
                    Log::warning('Daftra: public file download failed', [
                        'url' => $url,
                        'status' => $response->status(),
                        'with_api_key' => $withApiKey && (bool) $this->apiKey,
                    ]);

                    return null;
                }

                $body = $response->body();

                return ($body !== '' && strlen($body) >= 10) ? $body : null;
            };

            $candidates = $tryApiKeyFirst ? [true, false] : [false];

            foreach ($candidates as $withKey) {
                $body = $get($withKey);
                if ($body === null) {
                    continue;
                }
                $trimmed = ltrim($body, "\xEF\xBB\xBF \t\r\n");
                if (str_starts_with($trimmed, '%PDF')) {
                    return $body;
                }
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('Daftra: public file download exception', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function getSalesInvoiceJson(int $daftraInvoiceId): ?array
    {
        if (! $this->apiKey) {
            if (app()->environment('production')) {
                throw new \RuntimeException(
                    'Daftra integration is not configured: DAFTRA_API_KEY is missing.'
                );
            }

            return null;
        }

        $endpoint = "invoices/{$daftraInvoiceId}.json";
        $response = $this->salesInvoiceHttpGet($daftraInvoiceId);

        if ($response->failed()) {
            Log::error("Daftra API error [GET {$endpoint}]", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $json = $response->json();

        return is_array($json) ? $json : null;
    }

    /**
     * Authenticated GET api2/invoices/{id}.json (single place for invoice JSON fetch).
     */
    private function salesInvoiceHttpGet(int $daftraInvoiceId): \Illuminate\Http\Client\Response
    {
        $endpoint = "invoices/{$daftraInvoiceId}.json";
        $url = "{$this->baseUrl}/{$endpoint}";

        return Http::withHeaders([
            'APIKEY' => $this->apiKey,
            'Accept' => 'application/json',
        ])
            ->retry(
                times: 2,
                sleepMilliseconds: 1500,
                when: fn ($exception) => $exception instanceof \Illuminate\Http\Client\ConnectionException,
                throw: false,
            )
            ->get($url);
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    private function extractDaftraJsonMessage(?array $json): ?string
    {
        if ($json === null) {
            return null;
        }

        foreach (['message', 'error', 'msg', 'detail'] as $key) {
            $v = $json[$key] ?? null;
            if (is_string($v) && $v !== '') {
                return $v;
            }
        }

        $nested = data_get($json, 'data.message');
        if (is_string($nested) && $nested !== '') {
            return $nested;
        }

        return null;
    }

    private function extractInvoicePdfUrlFromPayload(array $json): ?string
    {
        foreach ([
            data_get($json, 'data.Invoice.invoice_pdf_url'),
            data_get($json, 'data.Invoice.pdf_url'),
            data_get($json, 'data.Invoice.pdf_download_url'),
            data_get($json, 'data.invoice_pdf_url'),
            data_get($json, 'Invoice.invoice_pdf_url'),
            data_get($json, 'Invoice.pdf_url'),
            data_get($json, 'invoice_pdf_url'),
            data_get($json, 'pdf_url'),
        ] as $candidate) {
            if (is_string($candidate) && filter_var($candidate, FILTER_VALIDATE_URL)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Tokenized / customer-facing browser URL from GET invoice JSON (not staff /owner/ links — those 401/404 for SP).
     * Field names vary by Daftra build; extend this list when you discover keys via {@see getSalesInvoiceRecord()}.
     */
    private function extractInvoicePublicBrowserUrlFromPayload(array $json): ?string
    {
        $paths = [
            'data.Invoice.public_link',
            'data.Invoice.share_link',
            'data.Invoice.customer_view_url',
            'data.Invoice.client_view_url',
            'data.Invoice.invoice_public_url',
            'data.Invoice.invoice_html_url',
            'data.Invoice.invoice_preview_url',
            'data.Invoice.public_url',
            'data.Invoice.share_url',
            'data.invoice.public_link',
            'data.invoice.share_link',
            'data.invoice.customer_view_url',
            'Invoice.public_link',
            'Invoice.share_link',
            'Invoice.customer_view_url',
            'Invoice.client_view_url',
            'Invoice.invoice_public_url',
            'Invoice.invoice_html_url',
            'Invoice.invoice_preview_url',
            'Invoice.public_url',
            'invoice.public_link',
            'public_link',
            'share_link',
            'customer_view_url',
            'client_view_url',
            'share_url',
            'public_url',
            'invoice_html_url',
            'invoice_preview_url',
            'invoice_view_url',
            'online_invoice_url',
            'external_view_url',
        ];

        foreach ($paths as $path) {
            $candidate = data_get($json, $path);
            if (! is_string($candidate) || ! filter_var($candidate, FILTER_VALIDATE_URL)) {
                continue;
            }
            if ($this->isDaftraStaffOwnerInvoiceUrl($candidate)) {
                continue;
            }

            return $candidate;
        }

        return null;
    }

    private function isDaftraStaffOwnerInvoiceUrl(string $url): bool
    {
        $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?? ''));

        return str_contains($host, 'daftra.com') && str_contains($url, '/owner/');
    }
}
