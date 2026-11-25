<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\Support\MocksFonnteGateway;
use Tests\TestCase;

/**
 * Feature 19: Broadcast WhatsApp Massal (F19)
 *
 * Source: dokum-ppdb §8.1, Build Plan §6.4, §12.1
 * Verifies mass WhatsApp broadcasting to filtered applicants (by admission status)
 * via Fonnte Gateway API with validation, role guard, and request batching.
 */
#[\PHPUnit\Framework\Attributes\Group('retired-ppdb')]
class F19_FonnteMassBroadcastTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, MocksFonnteGateway, RefreshDatabase;

    public function test_admin_can_access_broadcast_form_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.notifikasi'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.notifikasi');
    }

    public function test_admin_can_broadcast_whatsapp_to_all_applicants(): void
    {
        $this->mockFonnteSuccess();

        $admin = $this->createAdmin();
        $this->createPendaftaran(['no_hp' => '081111111111', 'status' => 'Menunggu']);
        $this->createPendaftaran(['no_hp' => '082222222222', 'status' => 'Lulus']);

        $response = $this->actingAs($admin)->post(route('admin.kirimNotifikasi'), [
            'target_status' => 'semua',
            'pesan' => 'Pemberitahuan resmi PPDB PPTQ Ibnu Juraimi.',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertFonnteSentCount(2);
    }

    public function test_admin_can_broadcast_whatsapp_filtered_by_lulus_status(): void
    {
        $this->mockFonnteSuccess();

        $admin = $this->createAdmin();
        $this->createPendaftaran(['no_hp' => '083333333333', 'status' => 'Lulus']);
        $this->createPendaftaran(['no_hp' => '084444444444', 'status' => 'Tidak Lulus']);

        $response = $this->actingAs($admin)->post(route('admin.kirimNotifikasi'), [
            'target_status' => 'Lulus',
            'pesan' => 'Selamat, Anda dinyatakan LULUS seleksi PPDB.',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertFonnteSent('083333333333', 'LULUS');
        $this->assertFonnteSentCount(1);
    }

    public function test_broadcast_validation_requires_message_and_target_status(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.kirimNotifikasi'), [
            'target_status' => '',
            'pesan' => '',
        ]);

        $response->assertSessionHasErrors(['target_status', 'pesan']);
        $this->assertFonnteNotSent();
    }

    public function test_non_admin_cannot_access_or_execute_whatsapp_broadcast(): void
    {
        $calonSantri = $this->createCalonSantri();

        $this->actingAs($calonSantri)->get(route('admin.notifikasi'))->assertStatus(403);

        $this->actingAs($calonSantri)->post(route('admin.kirimNotifikasi'), [
            'target_status' => 'semua',
            'pesan' => 'Unauthorized broadcast',
        ])->assertStatus(403);

        $this->assertFonnteNotSent();
    }
}
