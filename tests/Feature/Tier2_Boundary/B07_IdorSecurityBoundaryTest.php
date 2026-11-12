<?php

namespace Tests\Feature\Tier2_Boundary;

use App\Models\KegiatanPondok;
use App\Models\Santri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Tier 2 Boundary Test: B07 IDOR & Multi-Tenant Security Boundary
 *
 * Source: dokum-ppdb §6.4, dokum-cp1 §5.4, Build Plan §5.1, §6.2
 * Verifies horizontal and vertical privilege boundary isolation:
 * - Calon santri cannot download documents belonging to another applicant (403 Forbidden)
 * - Calon santri can download their own documents (200 OK)
 * - Wali murid cannot access records of children not linked to their account (403 Forbidden)
 * - Ustadz cannot submit presensi for an unassigned activity (403 Forbidden)
 * - Ustadz cannot input tahfidz setoran/murojaah for santri not in their binaan (403 Forbidden)
 * - Guest accessing private file download is redirected to login (302 / 401)
 */
class B07_IdorSecurityBoundaryTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /**
     * 1. Calon santri A attempting to download files of Calon santri B receives 403 Forbidden.
     */
    public function test_calon_santri_cannot_download_another_applicants_uploaded_documents(): void
    {
        $applicantA = $this->createCalonSantri(['name' => 'Applicant A']);
        $applicantB = $this->createCalonSantri(['name' => 'Applicant B']);

        Storage::disk('local')->put('pendaftaran/pas_photo/b.jpg', 'fake-image-b-content');

        $pendaftaranB = $this->createPendaftaran([
            'pas_photo' => 'pendaftaran/pas_photo/b.jpg',
        ], $applicantB);

        // Applicant A attempts to download B's file
        $response = $this->actingAs($applicantA)->get(
            route('user.file', ['id' => $pendaftaranB->id, 'field' => 'pas_photo'])
        );

        $response->assertForbidden();
    }

    /**
     * 2. Calon santri can download their own uploaded documents.
     */
    public function test_calon_santri_can_download_their_own_uploaded_documents(): void
    {
        $applicantA = $this->createCalonSantri(['name' => 'Applicant A']);

        Storage::disk('local')->put('pendaftaran/pas_photo/a.jpg', 'fake-image-a-content');

        $pendaftaranA = $this->createPendaftaran([
            'pas_photo' => 'pendaftaran/pas_photo/a.jpg',
        ], $applicantA);

        $response = $this->actingAs($applicantA)->get(
            route('user.file', ['id' => $pendaftaranA->id, 'field' => 'pas_photo'])
        );

        $response->assertStatus(200);
    }

    /**
     * 3. Wali murid cannot access reports or details of unlinked santri.
     */
    public function test_wali_murid_cannot_access_reports_or_data_of_unlinked_santri(): void
    {
        $waliA = $this->createWaliMurid(['name' => 'Wali A']);
        $waliB = $this->createWaliMurid(['name' => 'Wali B']);

        $santriB = Santri::create([
            'nama' => 'Anak Wali B',
            'nis' => '2026301',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7A',
            'wali_murid_id' => $waliB->id,
        ]);

        // Wali A attempts to view reports with santri_id of Wali B's child
        $responseWeekly = $this->actingAs($waliA)->get(
            route('wali-murid.laporan-mingguan.index', ['santri_id' => $santriB->id])
        );

        // Should either forbid or not include child B's records
        $this->assertTrue(
            $responseWeekly->status() === 403 ||
            ! str_contains($responseWeekly->getContent(), 'Anak Wali B')
        );

        // Direct show or edit attempt if accessible
        $responseHistory = $this->actingAs($waliA)->get(
            route('wali-murid.riwayat-kegiatan.index', ['santri_id' => $santriB->id])
        );

        $this->assertTrue(
            $responseHistory->status() === 403 ||
            ! str_contains($responseHistory->getContent(), 'Anak Wali B')
        );
    }

    /**
     * 4. Ustadz cannot submit presensi for an activity they are not assigned to.
     */
    public function test_ustadz_cannot_submit_presensi_for_activity_they_are_not_assigned_to(): void
    {
        $ustadz = $this->createUstadz();
        $unassignedKegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Kegiatan Tanpa Penugasan Ustadz Ini',
            'tingkatan' => 'semua',
        ]);

        $payload = [
            'kegiatan_pondok_id' => $unassignedKegiatan->id,
            'tanggal' => now()->toDateString(),
            'hari' => 'Senin',
            'jam_mulai' => '07:00',
            'latitude' => -7.797068,
            'longitude' => 110.370529,
        ];

        $response = $this->actingAs($ustadz)->post(route('ustadz.presensi-kegiatan.store'), $payload);

        $response->assertForbidden();
        $this->assertDatabaseMissing('presensi_kegiatan', [
            'kegiatan_pondok_id' => $unassignedKegiatan->id,
            'ustadz_id' => $ustadz->id,
        ]);
    }

    /**
     * 5. Ustadz cannot record setoran or murojaah for a santri not in their binaan.
     */
    public function test_ustadz_cannot_input_setoran_or_murojaah_for_santri_not_assigned_as_binaan(): void
    {
        $ustadz = $this->createUstadz();
        $unassignedSantri = Santri::create([
            'nama' => 'Santri Bukan Binaan',
            'nis' => '2026302',
            'tingkatan' => 'aliyah',
            'kelas' => '10',
        ]);

        $payload = [
            'santri_id' => $unassignedSantri->id,
            'tanggal' => now()->toDateString(),
            'juz' => 1,
            'surat' => 'Al-Baqarah',
            'ayat' => '1-10',
            'nilai' => 85.00,
        ];

        $responseSetoran = $this->actingAs($ustadz)->post(route('ustadz.setoran-hafalan.store'), $payload);
        $responseSetoran->assertForbidden();
        $this->assertDatabaseMissing('setoran_hafalan', ['santri_id' => $unassignedSantri->id]);

        $responseMurojaah = $this->actingAs($ustadz)->post(route('ustadz.murojaah.store'), $payload);
        $responseMurojaah->assertForbidden();
        $this->assertDatabaseMissing('murojaah', ['santri_id' => $unassignedSantri->id]);
    }

    /**
     * 6. Unauthenticated guest cannot access protected applicant document download.
     */
    public function test_unauthenticated_guest_cannot_download_private_ppdb_files(): void
    {
        $pendaftaran = $this->createPendaftaran([
            'pas_photo' => 'pendaftaran/pas_photo/guest_test.jpg',
        ]);

        $response = $this->get(route('user.file', ['id' => $pendaftaran->id, 'field' => 'pas_photo']));

        $response->assertRedirect(route('login'));
    }
}
