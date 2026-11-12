<?php

namespace Tests\Support;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

trait MocksFonnteGateway
{
    /**
     * Mock a successful Fonnte API response.
     *
     * @param  array<string, mixed>  $customResponse
     */
    protected function mockFonnteSuccess(array $customResponse = []): void
    {
        Http::fake([
            'https://api.fonnte.com/send*' => Http::response(array_merge([
                'status' => true,
                'target' => ['081234567890'],
                'message' => 'Pesan berhasil dikirim via Fonnte',
            ], $customResponse), 200),
        ]);
    }

    /**
     * Mock a failed Fonnte API response.
     */
    protected function mockFonnteFailure(int $statusCode = 500, string $errorMessage = 'Fonnte Gateway Error'): void
    {
        Http::fake([
            'https://api.fonnte.com/send*' => Http::response([
                'status' => false,
                'reason' => $errorMessage,
            ], $statusCode),
        ]);
    }

    /**
     * Assert that a message was sent via Fonnte to a specific target phone number.
     */
    protected function assertFonnteSent(string $targetPhone, ?string $messageContains = null): void
    {
        Http::assertSent(function (Request $request) use ($targetPhone, $messageContains) {
            $isFonnteUrl = str_contains($request->url(), 'api.fonnte.com/send');
            $hasAuthHeader = $request->hasHeader('Authorization');

            // Normalized comparison for target phone (accept 08xxx or 628xxx)
            $rawTarget = (string) ($request['target'] ?? $request->data()['target'] ?? '');
            $normalizedTarget = preg_replace('/^0/', '62', preg_replace('/[^\d]/', '', $rawTarget));
            $expectedNormalized = preg_replace('/^0/', '62', preg_replace('/[^\d]/', '', $targetPhone));

            $targetMatches = ($rawTarget === $targetPhone || $normalizedTarget === $expectedNormalized);

            $messageMatches = true;
            if ($messageContains !== null) {
                $rawMessage = (string) ($request['message'] ?? $request->data()['message'] ?? '');
                $messageMatches = str_contains($rawMessage, $messageContains);
            }

            return $isFonnteUrl && $hasAuthHeader && $targetMatches && $messageMatches;
        });
    }

    /**
     * Assert that no requests were sent to the Fonnte Gateway.
     */
    protected function assertFonnteNotSent(): void
    {
        Http::assertNotSent(function (Request $request) {
            return str_contains($request->url(), 'api.fonnte.com/send');
        });
    }

    /**
     * Assert the total number of requests sent to the Fonnte Gateway.
     */
    protected function assertFonnteSentCount(int $expectedCount): void
    {
        $sentRequests = Http::recorded(function (Request $request) {
            return str_contains($request->url(), 'api.fonnte.com/send');
        });

        $this->assertCount(
            $expectedCount,
            $sentRequests,
            "Expected {$expectedCount} requests to Fonnte Gateway, but ".count($sentRequests).' were sent.'
        );
    }
}
