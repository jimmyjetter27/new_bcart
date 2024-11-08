<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayStackService
{
    protected $baseUrl;
    protected $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.paystack.url');
        $this->secretKey = config('services.paystack.secret');
    }

    private function convertToPesewas($amount)
    {
        // Convert to pesewas (multiplying by 100)
        return intval($amount * 100);
    }

    protected function sendRequest(array $data, string $endpoint, $method = 'post')
    {
        try {
            $url = $this->baseUrl . $endpoint;

//            dd([
//                'url' => $url,
//                'endpoint' => $endpoint,
//                'method' => $method,
//                'data' => $data
//            ]);
            $response = Http::withToken($this->secretKey)
                ->acceptJson()
                ->$method($url, $data);

            // Log details for debugging
            $details = [
                'url' => $url,
                'payload' => $data,
                'response' => $response->json()
            ];
            Log::info('Paystack Request Details: ' . json_encode($details));

            // Handle error responses
            if (!$response->successful()) {
//                Log::error('Paystack Error: ' . $response->body());
//                return $response['message'] ?? null;
                return $response->json();
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Paystack Exception: ' . $e->getMessage());
            return null;
        }
    }

    public function initializePayment(array $data, $method = 'card')
    {
        // Convert amount from GHS to pesewas
        if (isset($data['amount'])) {
            $data['amount'] = $this->convertToPesewas($data['amount']);  // Converts GHS to pesewas
        }

        $data['callback_url'] = env('PAYSTACK_CALLBACK_URL');
        $data['method'] = $method;
        return $this->sendRequest($data, 'transaction/initialize');
    }

    public function verifyPayment(string $reference)
    {
        return $this->sendRequest([], "transaction/verify/{$reference}", 'get');
    }

    public function charge(array $data)
    {
        if (isset($data['amount'])) {
            $data['amount'] = $this->convertToPesewas($data['amount']);
        }
        return $this->sendRequest($data, 'charge');
    }

    public function chargeWithMobileMoney(array $data)
    {
        return $this->initializePayment($data, 'mobile_money');
    }

    public function chargeWithCard(array $data)
    {
        return $this->initializePayment($data, 'card');
    }

}
