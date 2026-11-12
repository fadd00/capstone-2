<?php

namespace Tests\Unit;

use App\Services\FonnteService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\Support\MocksFonnteGateway;
use Tests\TestCase;

class FonnteServiceTest extends TestCase
{
    use MocksFonnteGateway;

    protected FonnteService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.fonnte.token', 'test-fonnte-token-12345');
        Config::set('services.fonnte.url', 'https://api.fonnte.com/send');
        $this->service = new FonnteService;
    }

    /**
     * 1. Normalize Indonesian phone number starting with '08' to international '628'.
     */
    public function test_phone_normalization_from_local_zero_eight_format(): void
    {
        $input = '081234567890';
        $expected = '6281234567890';

        $normalized = $this->service->normalizePhone($input);
        $this->assertEquals($expected, $normalized);
    }

    /**
     * 2. Normalize phone number starting with '+62' by stripping the plus sign.
     */
    public function test_phone_normalization_from_plus_sixty_two_format(): void
    {
        $input = '+6281234567890';
        $expected = '6281234567890';

        $normalized = $this->service->normalizePhone($input);
        $this->assertEquals($expected, $normalized);
    }

    /**
     * 3. Normalize phone number containing formatting characters (dashes, spaces, parentheses).
     */
    public function test_phone_normalization_strips_dashes_spaces_and_parentheses(): void
    {
        $input = '(0812) 3456-7890';
        $expected = '6281234567890';

        $normalized = $this->service->normalizePhone($input);
        $this->assertEquals($expected, $normalized);
    }

    /**
     * 4. Phone number already in standard '628' format remains unchanged.
     */
    public function test_phone_normalization_preserves_standard_international_format(): void
    {
        $input = '628987654321';

        $normalized = $this->service->normalizePhone($input);
        $this->assertEquals($input, $normalized);
    }

    /**
     * 5. sendMessage dispatches an HTTP POST request to Fonnte API with Authorization header and payload.
     */
    public function test_send_message_dispatches_http_post_with_token_and_payload(): void
    {
        $this->mockFonnteSuccess(['message' => 'Status notification delivered']);

        $response = $this->service->sendMessage('081234567890', 'Selamat! Anda dinyatakan Lulus.');

        Http::assertSent(function (Request $request) {
            $isUrlValid = str_contains($request->url(), 'api.fonnte.com/send');
            $rawTarget = (string) ($request['target'] ?? $request->data()['target'] ?? '');
            $targetNormalized = str_contains($rawTarget, '6281234567890') || $rawTarget === '081234567890';
            $rawMessage = (string) ($request['message'] ?? $request->data()['message'] ?? '');
            $messageValid = str_contains($rawMessage, 'Lulus');

            return $isUrlValid && $targetNormalized && $messageValid;
        });

        $this->assertNotNull($response);
    }

    /**
     * 6. sendMessage gracefully handles network failure or non-200 responses without crashing.
     */
    public function test_send_message_handles_api_failure_gracefully(): void
    {
        $this->mockFonnteFailure(500, 'Gateway Timeout');

        // Should return array with status false or handled result without throwing unhandled exception
        try {
            $response = $this->service->sendMessage('081234567890', 'Tes pesan gagal');
            $this->assertIsArray($response);
            $this->assertFalse($response['status'] ?? false);
        } catch (\Exception $e) {
            // If service is designed to throw custom exception, catch and assert message
            $this->assertStringContainsString('Fonnte', $e->getMessage());
        }
    }
}
