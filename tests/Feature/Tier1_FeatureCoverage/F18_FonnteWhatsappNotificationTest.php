<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\Support\MocksFonnteGateway;
use Tests\TestCase;

/**
 * Feature 18: Notifikasi WhatsApp Fonnte (F18)
 *
 * Source: dokum-ppdb §8.1, Build Plan §12.1
 * Verifies automatic WhatsApp dispatch via Fonnte Gateway API upon applicant
 * status verification, phone normalization (08xx -> 628xx), and payload structure.
 */
#[\PHPUnit\Framework\Attributes\Group('retired-ppdb')]
class F18_FonnteWhatsappNotificationTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, MocksFonnteGateway, RefreshDatabase;

    public function test_fonnte_whatsapp_sent_when_admin_verifies_status_to_lulus(): void
    {
        $this->mockFonnteSuccess();

        $admin = $this->createAdmin();
        $pendaftar = $this->createPendaftaran([
            'nama_lengkap' => 'Muhammad Yusuf',
            'no_hp' => '081234567890',
            'status' => 'Menunggu',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.verifikasi', $pendaftar->id), [
            'status' => 'Lulus',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertFonnteSent('081234567890', 'Lulus');
    }

    public function test_fonnte_whatsapp_sent_when_admin_verifies_status_to_tidak_lulus(): void
    {
        $this->mockFonnteSuccess();

        $admin = $this->createAdmin();
        $pendaftar = $this->createPendaftaran([
            'nama_lengkap' => 'Fulan bin Fulan',
            'no_hp' => '081987654321',
            'status' => 'Menunggu',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.verifikasi', $pendaftar->id), [
            'status' => 'Tidak Lulus',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertFonnteSent('081987654321', 'Tidak Lulus');
    }

    public function test_fonnte_notification_normalizes_indonesian_phone_numbers(): void
    {
        $this->mockFonnteSuccess();

        $admin = $this->createAdmin();
        $pendaftar = $this->createPendaftaran([
            'no_hp' => '085711223344',
            'status' => 'Menunggu',
        ]);

        $this->actingAs($admin)->post(route('admin.verifikasi', $pendaftar->id), [
            'status' => 'Lulus',
        ]);

        $this->assertFonnteSent('6285711223344');
    }

    public function test_fonnte_message_contains_applicant_name_and_status(): void
    {
        $this->mockFonnteSuccess();

        $admin = $this->createAdmin();
        $pendaftar = $this->createPendaftaran([
            'nama_lengkap' => 'Santriwati Khadijah',
            'no_hp' => '081300001111',
            'status' => 'Menunggu',
        ]);

        $this->actingAs($admin)->post(route('admin.verifikasi', $pendaftar->id), [
            'status' => 'Lulus',
        ]);

        $this->assertFonnteSent('081300001111', 'Khadijah');
    }

    public function test_verification_transaction_succeeds_even_if_fonnte_gateway_fails(): void
    {
        // Mock gateway returning 500 server error
        $this->mockFonnteFailure(500, 'Fonnte Server Down');

        $admin = $this->createAdmin();
        $pendaftar = $this->createPendaftaran([
            'status' => 'Menunggu',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.verifikasi', $pendaftar->id), [
            'status' => 'Lulus',
        ]);

        // Status update in database must remain persisted (resilient against network glitches)
        $this->assertDatabaseHas('pendaftarans', [
            'id' => $pendaftar->id,
            'status' => 'Lulus',
        ]);
    }
}
