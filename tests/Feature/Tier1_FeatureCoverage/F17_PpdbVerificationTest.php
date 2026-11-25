<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\Support\MocksFonnteGateway;
use Tests\TestCase;

/**
 * Feature 17: Verifikasi Status Pendaftar (F17)
 *
 * Source: dokum-ppdb §5.3 Q3, Build Plan §6.4
 * Verifies admin review and verification workflow updating applicant status
 * (Menunggu, Lulus, Tidak Lulus) with authorization and validation constraints.
 */
#[\PHPUnit\Framework\Attributes\Group('retired-ppdb')]
class F17_PpdbVerificationTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, MocksFonnteGateway, RefreshDatabase;

    public function test_admin_can_verify_applicant_status_to_lulus(): void
    {
        $this->mockFonnteSuccess();

        $admin = $this->createAdmin();
        $pendaftar = $this->createPendaftaran(['status' => 'Menunggu']);

        $response = $this->actingAs($admin)->post(route('admin.verifikasi', $pendaftar->id), [
            'status' => 'Lulus',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('pendaftarans', [
            'id' => $pendaftar->id,
            'status' => 'Lulus',
        ]);
    }

    public function test_admin_can_verify_applicant_status_to_tidak_lulus(): void
    {
        $this->mockFonnteSuccess();

        $admin = $this->createAdmin();
        $pendaftar = $this->createPendaftaran(['status' => 'Menunggu']);

        $response = $this->actingAs($admin)->post(route('admin.verifikasi', $pendaftar->id), [
            'status' => 'Tidak Lulus',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('pendaftarans', [
            'id' => $pendaftar->id,
            'status' => 'Tidak Lulus',
        ]);
    }

    public function test_admin_can_reset_applicant_status_back_to_menunggu(): void
    {
        $this->mockFonnteSuccess();

        $admin = $this->createAdmin();
        $pendaftar = $this->createPendaftaran(['status' => 'Lulus']);

        $response = $this->actingAs($admin)->post(route('admin.verifikasi', $pendaftar->id), [
            'status' => 'Menunggu',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('pendaftarans', [
            'id' => $pendaftar->id,
            'status' => 'Menunggu',
        ]);
    }

    public function test_verification_rejects_invalid_status_enum(): void
    {
        $admin = $this->createAdmin();
        $pendaftar = $this->createPendaftaran(['status' => 'Menunggu']);

        $response = $this->actingAs($admin)->post(route('admin.verifikasi', $pendaftar->id), [
            'status' => 'DiterimaSebagian', // Invalid enum
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('pendaftarans', [
            'id' => $pendaftar->id,
            'status' => 'Menunggu',
        ]);
    }

    public function test_non_admin_cannot_verify_applicant_status(): void
    {
        $calonSantri = $this->createCalonSantri();
        $pendaftar = $this->createPendaftaran(['status' => 'Menunggu']);

        $response = $this->actingAs($calonSantri)->post(route('admin.verifikasi', $pendaftar->id), [
            'status' => 'Lulus',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('pendaftarans', [
            'id' => $pendaftar->id,
            'status' => 'Menunggu',
        ]);
    }

    public function test_verification_of_non_existent_applicant_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.verifikasi', 999999), [
            'status' => 'Lulus',
        ]);

        $response->assertStatus(404);
    }
}
