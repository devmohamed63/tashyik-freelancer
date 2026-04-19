<?php

namespace App\Utils\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Utils\Services\Daftra\DTOs\InvoiceDTO;
use App\Utils\Services\Daftra\DTOs\ProductDTO;
use App\Utils\Services\Daftra\DTOs\PaymentDTO;
use App\Utils\Services\Daftra\DTOs\CreditNoteDTO;

class Daftra
{
    private string $baseUrl;
    private string $apiKey;

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
        // Skip calling API if no key is set yet
        if (!$this->apiKey) return null;

        $response = Http::withHeaders([
            'APIKEY' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])
        ->retry(3, 2000) // Retry 3 times, wait 2 seconds between each
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
     */
    public function syncClient(User $user): ?int
    {
        if ($user->hasDaftraId()) {
            return $user->daftra_id;
        }

        $typeId = $user->isInstitutionOrCompany() ? 2 : 1; // 1=individual, 2=company

        $response = $this->request('post', 'clients', [
            'Client' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone1' => $user->phone,
                'type' => $typeId,
                'notes' => "Synced from Semiona | User #{$user->id}",
            ],
        ]);

        if ($response && isset($response['id'])) {
            $daftraId = $response['id'];
        } elseif ($response && isset($response['Client']['id'])) {
            $daftraId = $response['Client']['id'];
        } else {
            return null;
        }

        $user->setDaftraId($daftraId);
        Log::info("Daftra: Created client #{$daftraId} for user #{$user->id}");

        return $daftraId;
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
