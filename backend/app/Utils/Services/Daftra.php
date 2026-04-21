<?php

namespace App\Utils\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Utils\Services\Daftra\DTOs\InvoiceDTO;
use App\Utils\Services\Daftra\DTOs\ProductDTO;
use App\Utils\Services\Daftra\DTOs\PaymentDTO;
use App\Utils\Services\Daftra\DTOs\CreditNoteDTO;

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
     * Make an authenticated request to the Daftra API with Built-in Retry (Queue Resilience).
     */
    private function request(string $method, string $endpoint, array $data = [])
    {
        // Fail loud in production so missing credentials surface immediately
        // via the queue's failed_jobs table instead of silently succeeding.
        if (!$this->apiKey) {
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
                'body'   => $response->body(),
                'data'   => $data,
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
                $email = $user->email ?: "user-{$user->id}@noemail.semiona.local";
                $phone = $user->phone ?: '-';
                $name  = $user->name  ?: "User #{$user->id}";

                $response = $this->request('post', 'clients', [
                    'Client' => [
                        'first_name' => $name,
                        'email'      => $email,
                        'phone1'     => $phone,
                        'type'       => $typeId,
                        'notes'      => "Synced from Semiona | User #{$user->id}",
                    ],
                ]);

                $daftraId = $response['id']
                    ?? $response['Client']['id']
                    ?? null;

                if (!$daftraId) {
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
}
