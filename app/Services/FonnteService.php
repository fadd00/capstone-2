<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class FonnteService
{
    protected string $token;

    protected string $url;

    public function __construct()
    {
        $this->token = config('services.fonnte.token', '');
        $this->url = config('services.fonnte.url', 'https://api.fonnte.com/send');
    }

    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/[^\d]/', '', $phone);

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        return $digits;
    }

    public function sendMessage(string $target, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post($this->url, [
                'target' => $this->normalizePhone($target),
                'message' => $message,
            ]);

            return $response->json() ?? ['status' => $response->successful()];
        } catch (Throwable $e) {
            Log::error('Fonnte API dispatch error: '.$e->getMessage());

            return ['status' => false, 'error' => $e->getMessage()];
        }
    }
}
