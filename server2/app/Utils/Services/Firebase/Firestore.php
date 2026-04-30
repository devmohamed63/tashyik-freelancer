<?php

namespace App\Utils\Services\Firebase;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Firestore
{
    protected string $endpoint;

    protected string $project;

    protected string $database;

    protected string $projectId;

    protected string $clientEmail;

    protected string $privateKey;

    protected array $headers;

    public function __construct()
    {
        $credentials = json_decode(
            file_get_contents(storage_path('app/private/google-service-account.json')),
            true
        );

        $this->endpoint = 'https://firestore.googleapis.com';
        $this->projectId = $credentials['project_id'];
        $this->clientEmail = $credentials['client_email'];
        $this->privateKey = $credentials['private_key'];
        $this->database = '(default)';

        $this->getAccessToken();
    }

    private function getAccessToken(): void
    {
        $now = time();

        $payload = [
            'iss'   => $this->clientEmail,
            'scope' => 'https://www.googleapis.com/auth/datastore',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ];

        $jwt = JWT::encode($payload, $this->privateKey, 'RS256');

        $response = Http::asForm()->post(
            'https://oauth2.googleapis.com/token',
            [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]
        );

        $this->headers = [
            'Authorization' => 'Bearer ' . $response['access_token'],
            'Content-Type' => 'application/json',
        ];
    }

    public function runWrites(array $writes): void
    {
        // Request url
        $url = "$this->endpoint/v1/projects/$this->projectId/databases/$this->database/documents:commit";

        $response = Http::withHeaders($this->headers)
            ->post($url, ['writes' => $writes]);

        if ($response->failed()) {
            Log::error('Firestore failed to update document:', $response->json());
        }
    }

    public function getDocuments(string $path): array
    {
        // Request url
        $url = "$this->endpoint/v1/projects/$this->projectId/databases/$this->database/documents/$path";

        $response = Http::withHeaders($this->headers)->get($url);

        if ($response->failed()) {
            Log::error('Firestore failed to get documents list:', $response->json());
        }

        return $response->json('documents') ?? [];
    }

    public function getDocument(string $path): ?array
    {
        // Request url
        $url = "$this->endpoint/v1/projects/$this->projectId/databases/$this->database/documents/$path";

        $response = Http::withHeaders($this->headers)->get($url);

        if ($response->failed()) {
            Log::error('Firestore failed to get single document:', $response->json());
        }

        return $response->json();
    }

    public function updateDocument(string $path, array $fields): void
    {
        // Request url
        $url = "$this->endpoint/v1/projects/$this->projectId/databases/$this->database/documents/$path";

        $response = Http::withHeaders($this->headers)
            ->patch($url, compact('fields'));

        if ($response->failed()) {
            Log::error('Firestore failed to update document:', $response->json());
        }
    }

    public function incrementFields(array $fields)
    {
        // Request url
        $url = "$this->endpoint/v1/projects/$this->projectId/databases/$this->database/documents:commit";

        $writes = [];

        foreach ($fields as $field) {
            array_push($writes, [
                'transform' => [
                    'document' => "projects/$this->projectId/databases/$this->database/documents/$field[0]",
                    'fieldTransforms' => [
                        [
                            'fieldPath' => $field[1],
                            'increment' => ['integerValue' => $field[2] ?? 1]
                        ]
                    ]
                ]
            ]);
        }

        $data = ['writes' => [$writes]];

        $response = Http::withHeaders($this->headers)->post($url, $data);

        if ($response->failed()) {
            Log::error('Firestore failed to increment field:', $response->json());
        }
    }
}
