<?php

namespace App\Utils\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class Paymob
{
    private string $baseUrl;

    private string $currency;

    private array $paymentMethods;

    private string $apiKey;

    private string $publicKey;

    private string $secretKey;

    private string $clientSecret;

    private string $webhookUrl;

    private string $callbackUrl;

    private string $authToken;

    private int $amount;

    private string|array|null $reference = null;

    public function __construct()
    {
        $this->baseUrl = env("PAYMOB_ENDPOINT");
        $this->currency = env("PAYMOB_CURRENCY");
        $this->apiKey = env("PAYMOB_API_KEY");
        $this->publicKey = env("PAYMOB_PUBLIC_KEY");
        $this->secretKey = env("PAYMOB_SECRET_KEY");
        $this->paymentMethods = explode(',', env("PAYMOB_PAYMENT_METHODS"));
        $this->paymentMethods = array_map(fn($method) => (int) $method, $this->paymentMethods);
        $this->webhookUrl = route('api.webhook.paymob');
        $this->callbackUrl = env('FRONTEND_URL') . '/ar/orders';
    }

    private function setAmount($amount): static
    {
        $this->amount = (int) round($amount * 100);

        return $this;
    }

    public function setReference($reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    private function authenticate(): void
    {
        $response = Http::post("$this->baseUrl/api/auth/tokens", [
            'api_key' => $this->apiKey
        ]);

        if ($response->successful()) {
            $this->authToken = $response->json()['token'];
        } else {
            throw new \Exception("Paymob error. Failed to get authentication token: " . $response->body());
        }
    }

    private function retrieveTransaction(int $id)
    {
        $response = Http::withHeader('Authorization', "Bearer $this->authToken")
            ->get("$this->baseUrl/api/acceptance/transactions/{$id}");

        if ($response->successful()) return $response->json();

        if ($response->status() == 404) return [
            'data' => [
                'message' => $response->json()['detail']
            ]
        ];

        throw new \Exception("Paymob error. Failed to retrieve transaction: " . $response->body());
    }

    public function createPaymentRequest()
    {
        $user = Auth::user();

        $response = Http::withHeader('Authorization', "Token $this->secretKey")
            ->post("$this->baseUrl/v1/intention", [
                'amount' => $this->amount,
                'currency' => $this->currency,
                'payment_methods' => $this->paymentMethods,
                'items' => [],
                'billing_data' => [
                    'apartment'       => 'NA',
                    'email'           => 'customer@example.com',
                    'floor'           => 'NA',
                    'first_name'      => $user->name ?? 'NA',
                    'street'          => 'NA',
                    'building'        => 'NA',
                    'phone_number'    => $user->phone ?? 0000000000,
                    'shipping_method' => 'PKG',
                    'postal_code'     => 'NA',
                    'city'            => 'NA',
                    'country'         => 'NA',
                    'last_name'       => 'NA',
                    'state'           => 'NA'
                ],
                'extras' => [
                    'reference' => $this->reference,
                ],
                'expiration' => 3600,
                'notification_url' => $this->webhookUrl,
                'redirection_url' => $this->callbackUrl,
            ]);

        if ($response->successful()) {
            $this->clientSecret = $response->json()['client_secret'];
        } else {
            throw new \Exception("Paymob error. Failed to get payment key: " . $response->body());
        }
    }

    public function getPaymentLink($amount)
    {
        try {
            $this->setAmount($amount)->createPaymentRequest();

            return "$this->baseUrl/unifiedcheckout/?publicKey=$this->publicKey&clientSecret=$this->clientSecret";
        } catch (\Throwable $th) {
            throw new \Exception("Paymob error. Failed to get payment link: $th");
        }
    }

    public function getTransactionStatus(int $id)
    {
        $this->authenticate();

        $transaction = $this->retrieveTransaction($id);

        return [
            'success' => $transaction['success'] ?? false,
            'message' => $transaction['data']['message'] ?? '',
        ];
    }
}
