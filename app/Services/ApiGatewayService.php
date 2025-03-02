<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ApiGatewayService {
    private $apiGatewayUrl;
    private $apiGatewayKey;

    public function __construct()
    {
        $this->apiGatewayUrl = env('AWS_API_GATEWAY_URL');
        $this->apiGatewayKey = env('AWS_API_GATEWAY_KEY');
    }

    public function postToApiGateway($endpoint, $data)
    {
        $headers = [
            'x-api-key' => $this->apiGatewayKey,
            'Content-Type' => 'application/json'
        ];

        $response = Http::withHeaders($headers)->post($this->apiGatewayUrl . $endpoint, $data);
        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}