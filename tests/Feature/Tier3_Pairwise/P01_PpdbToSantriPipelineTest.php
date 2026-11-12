<?php

namespace Tests\Feature\Tier3_Pairwise;

use App\Models\Pendaftaran;
use App\Models\Santri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\Support\MocksFonnteGateway;
use Tests\TestCase;

/**
 * Tier 3 Pairwise Test: P01 PPDB to Santri Pipeline
 *
 * Source: dokum-ppdb §1.2, §5.3 Q3, dokum-cp1 §4, Build Plan §11
 * Verifies cross-module pipeline between Online Admission (PPDB) and Academic Management:
 * 1. Public Form Submission -> Auto Account Generation
 * 2. Admin Verification (Lulus) -> Fonnte WhatsApp Notification
 * 3. Promotion Service Execution -> Creation of Santri record with NIS
 * 4. Linking pendaftar->santri_id & wali_murid_id
 * 5. Prevention of duplicate promotion and non-Lulus promotion
 */
class P01_PpdbToSantriPipelineTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, MocksFonnteGateway, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->mockFonnteSuccess();
        $this->createStatusForm('Buka');
    }

    /**
     * 1. Full end-to-end registration -> verification -> promotion pipeline.
     */
    public function test_end_to_end_registration_to_verification_to_santri_promotion_pipeline(): void
    {
        // Step 1: Guest submits registration form
        $files = $this->fakeRequiredPpdbFiles();
        $formData = $this->validPpdbFormData([
            'nama_lengkap' => 'Fadhil Ar-Razi',
            'email' => 'fadhil_'.uniqid().'@example.com',
            'no_hp' => '081298765432',
            'tanggal_lahir' => '2012-08-17',
        ]);

        $responseRegister = $this->post(route('pendaftaran.store'), array_merge($formData, $files));
        $responseRegister->assertRedirect(route('register.success'));

        $pendaftar = Pendaftaran::where('email', $formData['email'])->firstOrFail();
        $this->assertEquals('Menunggu', $pendaftar->status);
        $this->assertNull($pendaftar->santri_id);

        // Step 2: Admin logs in, verifies applicant as 'Lulus'
        $admin = $this->createAdmin();

        $responseVerify = $this->actingAs($admin)->post(route('admin.verifikasi', $pendaftar->id), [
            'status' => 'Lulus',
            'catatan' => 'Lulus tes baca Qur’an dan wawancara.',
        ]);
        $responseVerify->assertSessionHasNoErrors();

        $pendaftar->refresh();
        $this->assertEquals('Lulus', $pendaftar->status);

        // Assert Fonnte notification was triggered
        $this->assertFonnteSent($pendaftar->no_hp, 'Lulus');

        // Step 3: Admin promotes verified applicant to Santri Akademik with NIS 2026001
        $responsePromote = $this->actingAs($admin)->post(route('admin.pendaftar.promosikan', $pendaftar->id), [
            'nis' => '2026001',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7A',
        ]);
        $responsePromote->assertSessionHasNoErrors();
        $responsePromote->assertRedirect();

        // Step 4: Verify Santri record in database and linkage
        $this->assertDatabaseHas('santri', [
            'nama' => 'Fadhil Ar-Razi',
            'nis' => '2026001',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7A',
            'tanggal_lahir' => '2012-08-17',
        ]);

        $santri = Santri::where('nis', '2026001')->firstOrFail();
        $pendaftar->refresh();
        $this->assertEquals($santri->id, $pendaftar->santri_id);
    }

    /**
     * 2. Promotion pipeline linking to Wali Murid account.
     */
    public function test_promotion_pipeline_with_wali_murid_account_linking(): void
    {
        $admin = $this->createAdmin();
        $waliMurid = $this->createWaliMurid(['name' => 'Bapak Hendra']);

        $pendaftar = $this->createPendaftaran([
            'nama_lengkap' => 'Muhammad Zaid',
            'status' => 'Lulus',
            'santri_id' => null,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pendaftar.promosikan', $pendaftar->id), [
            'nis' => '2026002',
            'tingkatan' => 'aliyah',
            'kelas' => '10 IPA',
            'wali_murid_id' => $waliMurid->id,
        ]);

        $response->assertSessionHasNoErrors();

        $santri = Santri::where('nis', '2026002')->firstOrFail();
        $this->assertEquals($waliMurid->id, $santri->wali_murid_id);

        // Bidirectional relationship verification
        $this->assertTrue($waliMurid->santri()->where('id', $santri->id)->exists());
    }

    /**
     * 3. Pipeline rejects promotion if applicant status is not 'Lulus'.
     */
    public function test_pipeline_rejects_promotion_if_applicant_not_verified_lulus(): void
    {
        $admin = $this->createAdmin();

        $pendaftarMenunggu = $this->createPendaftaran([
            'status' => 'Menunggu',
            'santri_id' => null,
        ]);

        $responseMenunggu = $this->actingAs($admin)->post(route('admin.pendaftar.promosikan', $pendaftarMenunggu->id), [
            'nis' => '2026003',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);

        $this->assertTrue($responseMenunggu->isClientError() || $responseMenunggu->isRedirection());
        $this->assertDatabaseMissing('santri', ['nis' => '2026003']);

        $pendaftarTidakLulus = $this->createPendaftaran([
            'status' => 'Tidak Lulus',
            'santri_id' => null,
        ]);

        $responseTidakLulus = $this->actingAs($admin)->post(route('admin.pendaftar.promosikan', $pendaftarTidakLulus->id), [
            'nis' => '2026004',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);

        $this->assertTrue($responseTidakLulus->isClientError() || $responseTidakLulus->isRedirection());
        $this->assertDatabaseMissing('santri', ['nis' => '2026004']);
    }

    /**
     * 4. Pipeline prevents duplicate promotion and rejects conflicting NIS.
     */
    public function test_pipeline_prevents_duplicate_promotion_and_retains_data_integrity(): void
    {
        $admin = $this->createAdmin();

        $pendaftar = $this->createPendaftaran([
            'nama_lengkap' => 'Calon Dipromosikan',
            'status' => 'Lulus',
            'santri_id' => null,
        ]);

        // First promotion succeeds
        $this->actingAs($admin)->post(route('admin.pendaftar.promosikan', $pendaftar->id), [
            'nis' => '2026005',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ])->assertSessionHasNoErrors();

        $pendaftar->refresh();
        $this->assertNotNull($pendaftar->santri_id);

        // Second promotion attempt with same or new NIS fails
        $responseDuplicate = $this->actingAs($admin)->post(route('admin.pendaftar.promosikan', $pendaftar->id), [
            'nis' => '2026006',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);

        $this->assertTrue($responseDuplicate->isClientError() || $responseDuplicate->isRedirection());
        $this->assertDatabaseMissing('santri', ['nis' => '2026006']);
    }
}
