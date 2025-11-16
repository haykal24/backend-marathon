<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected ?string $apiKey;
    protected ?string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.waajo.api_key');
        $this->baseUrl = config('services.waajo.base_url');
    }

    public function sendMessage(string $to, string $message): ?array
    {
        if (empty($this->apiKey) || empty($this->baseUrl)) {
            Log::error('Waajo configuration is missing.');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->post(rtrim($this->baseUrl, '/') . '/send-text', [
                'recipient_number' => $this->formatPhoneNumber($to),
                'text' => $message,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to send WhatsApp message', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Exception while sending WhatsApp message', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function formatPhoneNumber(string $phoneNumber): string
    {
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        if (str_starts_with($phoneNumber, '0')) {
            $phoneNumber = '62' . substr($phoneNumber, 1);
        }
        return $phoneNumber;
    }
}


