<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\Santri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 27: Promosi Pendaftar Lulus ke Santri Akademik (F27)
 *
 * Source: dokum-ppdb §1.2, dokum-cp1 §4, Build Plan §11
 * Minimum 5 test methods covering:
 * - Happy path promotion with valid NIS and grade
 * - Linking optional wali murid on promotion
 * - Rejection of unverified / non-Lulus applicants
 * - Rejection of already promoted applicants
 * - Unique NIS validation enforcement
 * - Strict role authorization (403 for non-admin)
 */
#[\PHPUnit\Framework\Attributes\Group('retired-ppdb')]
class F27_PendaftarPromotionWorkflowTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, RefreshDatabase;

    public function test_admin_can_promote_verified_lulus_pendaftar_to_santri_with_nis(): void
    {
        $admin = $this->createAdmin();
        $pendaftar = $this->createPendaftaran([
            'nama_lengkap' => 'Ahmad Dahlan',
            'status' => 'Lulus',
            'santri_id' => null,
            'tanggal_lahir' => '2012-04-10',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pendaftar.promosikan', $pendaftar->id), [
            'nis' => '2026001',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7A',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('santri', [
            'nama' => 'Ahmad Dahlan',
            'nis' => '2026001',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7A',
            'tanggal_lahir' => '2012-04-10',
        ]);

        $santri = Santri::where('nis', '2026001')->firstOrFail();
        $pendaftar->refresh();

        $this->assertEquals($santri->id, $pendaftar->santri_id);
    }

    public function test_promotion_links_optional_wali_murid_when_provided(): void
    {
        $admin = $this->createAdmin();
        $wali = $this->createWaliMurid(['name' => 'Bapak Dahlan']);
        $pendaftar = $this->createPendaftaran([
            'nama_lengkap' => 'Fatimah Az-Zahra',
            'status' => 'Lulus',
            'santri_id' => null,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pendaftar.promosikan', $pendaftar->id), [
            'nis' => '2026002',
            'tingkatan' => 'aliyah',
            'kelas' => '10 IPA',
            'wali_murid_id' => $wali->id,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('santri', [
            'nis' => '2026002',
            'wali_murid_id' => $wali->id,
            'tingkatan' => 'aliyah',
        ]);
    }

    public function test_cannot_promote_pendaftar_with_status_menunggu_or_tidak_lulus(): void
    {
        $admin = $this->createAdmin();
        $pendaftarMenunggu = $this->createPendaftaran([
            'nama_lengkap' => 'Calon Menunggu',
            'status' => 'Menunggu',
            'santri_id' => null,
        ]);

        $responseMenunggu = $this->actingAs($admin)->post(route('admin.pendaftar.promosikan', $pendaftarMenunggu->id), [
            'nis' => '2026003',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);

        $this->assertTrue(
            $responseMenunggu->isClientError() || $responseMenunggu->isRedirection(),
            'Expected rejection response for unverified applicant'
        );
        $this->assertDatabaseMissing('santri', ['nis' => '2026003']);

        $pendaftarGagal = $this->createPendaftaran([
            'nama_lengkap' => 'Calon Tidak Lulus',
            'status' => 'Tidak Lulus',
            'santri_id' => null,
        ]);

        $responseGagal = $this->actingAs($admin)->post(route('admin.pendaftar.promosikan', $pendaftarGagal->id), [
            'nis' => '2026004',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);

        $this->assertTrue(
            $responseGagal->isClientError() || $responseGagal->isRedirection(),
            'Expected rejection response for non-Lulus applicant'
        );
        $this->assertDatabaseMissing('santri', ['nis' => '2026004']);
    }

    public function test_cannot_promote_already_promoted_pendaftar(): void
    {
        $admin = $this->createAdmin();
        $existingSantri = Santri::create([
            'nama' => 'Santri Eksis',
            'nis' => '2026005',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);

        $pendaftar = $this->createPendaftaran([
            'nama_lengkap' => 'Santri Eksis',
            'status' => 'Lulus',
            'santri_id' => $existingSantri->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pendaftar.promosikan', $pendaftar->id), [
            'nis' => '2026006',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);

        $this->assertTrue(
            $response->isClientError() || $response->isRedirection(),
            'Expected rejection response for already promoted applicant'
        );
        $this->assertDatabaseMissing('santri', ['nis' => '2026006']);
    }

    public function test_cannot_promote_with_duplicate_or_invalid_nis(): void
    {
        $admin = $this->createAdmin();
        Santri::create([
            'nama' => 'Santri Pertama',
            'nis' => '2026010',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);

        $pendaftar = $this->createPendaftaran([
            'nama_lengkap' => 'Santri Kedua',
            'status' => 'Lulus',
            'santri_id' => null,
        ]);

        // Duplicate NIS
        $responseDuplicate = $this->actingAs($admin)->post(route('admin.pendaftar.promosikan', $pendaftar->id), [
            'nis' => '2026010',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);

        $responseDuplicate->assertSessionHasErrors(['nis']);

        // Empty NIS
        $responseEmpty = $this->actingAs($admin)->post(route('admin.pendaftar.promosikan', $pendaftar->id), [
            'nis' => '',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);

        $responseEmpty->assertSessionHasErrors(['nis']);
    }

    public function test_non_admin_cannot_promote_pendaftar(): void
    {
        $ustadz = $this->createUstadz();
        $wali = $this->createWaliMurid();
        $calon = $this->createCalonSantri();

        $pendaftar = $this->createPendaftaran([
            'status' => 'Lulus',
            'santri_id' => null,
        ]);

        $payload = [
            'nis' => '2026099',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ];

        $this->actingAs($ustadz)->post(route('admin.pendaftar.promosikan', $pendaftar->id), $payload)
            ->assertForbidden();

        $this->actingAs($wali)->post(route('admin.pendaftar.promosikan', $pendaftar->id), $payload)
            ->assertForbidden();

        $this->actingAs($calon)->post(route('admin.pendaftar.promosikan', $pendaftar->id), $payload)
            ->assertForbidden();

        $this->assertDatabaseMissing('santri', ['nis' => '2026099']);
    }
}
