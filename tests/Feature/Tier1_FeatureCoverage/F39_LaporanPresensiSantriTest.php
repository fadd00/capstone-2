<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\KegiatanPondok;
use App\Models\PresensiKegiatan;
use App\Models\PresensiSantri;
use App\Models\Santri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 39: Laporan Presensi Santri Global (F39)
 *
 * Source: dokum-cp1 §5.2 Q-ADM-12, Build Plan §10.2
 * Minimum 5 test methods covering:
 * - Viewing global santri presensi report
 * - Filtering by attendance status (hadir/izin/sakit/alpha) and tingkatan
 * - Filtering by specific santri and kegiatan
 * - Exporting report to PDF
 * - Exporting report to Excel
 * - Role-based authorization guard (403 for non-admin)
 */
class F39_LaporanPresensiSantriTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    protected function setupAttendanceFixtures(): array
    {
        $ustadz = $this->createUstadz();
        $kegiatan = KegiatanPondok::create(['nama_kegiatan' => 'Tahfidz Pagi', 'tingkatan' => 'tsanawiyah', 'kelas' => '7']);

        $session = PresensiKegiatan::create([
            'kegiatan_pondok_id' => $kegiatan->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-12',
            'hari' => 'Kamis',
            'jam_mulai' => '05:30:00',
            'status' => 'valid',
        ]);

        $santri1 = Santri::create(['nama' => 'Santri Hadir', 'nis' => '2026070', 'tingkatan' => 'tsanawiyah', 'kelas' => '7']);
        $santri2 = Santri::create(['nama' => 'Santri Alpha', 'nis' => '2026071', 'tingkatan' => 'tsanawiyah', 'kelas' => '7']);

        PresensiSantri::create(['presensi_kegiatan_id' => $session->id, 'santri_id' => $santri1->id, 'status' => 'hadir']);
        PresensiSantri::create(['presensi_kegiatan_id' => $session->id, 'santri_id' => $santri2->id, 'status' => 'alpha']);

        return [$santri1, $santri2, $kegiatan];
    }

    public function test_admin_can_view_presensi_santri_report_page(): void
    {
        $admin = $this->createAdmin();
        $this->setupAttendanceFixtures();

        $response = $this->actingAs($admin)->get(route('admin.laporan.presensi-santri'));

        $response->assertOk();
        $response->assertSee('Santri Hadir');
        $response->assertSee('Santri Alpha');
    }

    public function test_admin_can_filter_report_by_status_and_tingkatan(): void
    {
        $admin = $this->createAdmin();
        $this->setupAttendanceFixtures();

        // Filter only 'hadir'
        $responseHadir = $this->actingAs($admin)->get(route('admin.laporan.presensi-santri', [
            'status' => 'hadir',
            'tingkatan' => 'tsanawiyah',
        ]));

        $responseHadir->assertOk();
        $responseHadir->assertSee('Santri Hadir');
        $responseHadir->assertDontSee('Santri Alpha');
    }

    public function test_admin_can_filter_report_by_specific_santri_and_kegiatan(): void
    {
        $admin = $this->createAdmin();
        [$santri1, $santri2, $kegiatan] = $this->setupAttendanceFixtures();

        $response = $this->actingAs($admin)->get(route('admin.laporan.presensi-santri', [
            'santri_id' => $santri1->id,
            'kegiatan_pondok_id' => $kegiatan->id,
        ]));

        $response->assertOk();
        $response->assertSee('Santri Hadir');
        $response->assertDontSee('Santri Alpha');
    }

    public function test_admin_can_export_presensi_santri_report_to_pdf(): void
    {
        $admin = $this->createAdmin();
        $this->setupAttendanceFixtures();

        $response = $this->actingAs($admin)->get(route('admin.laporan.export', [
            'laporan' => 'presensi-santri',
            'format' => 'pdf',
        ]));

        $this->assertTrue(
            $response->status() === 200 || $response->isRedirection(),
            'Expected PDF export response'
        );
    }

    public function test_admin_can_export_presensi_santri_report_to_excel(): void
    {
        $admin = $this->createAdmin();
        $this->setupAttendanceFixtures();

        $response = $this->actingAs($admin)->get(route('admin.laporan.export', [
            'laporan' => 'presensi-santri',
            'format' => 'excel',
        ]));

        $this->assertTrue(
            $response->status() === 200 || $response->isRedirection(),
            'Expected Excel export response'
        );
    }

    public function test_non_admin_cannot_access_or_export_presensi_santri_report(): void
    {
        $ustadz = $this->createUstadz();
        $wali = $this->createWaliMurid();
        $calon = $this->createCalonSantri();

        $this->actingAs($ustadz)->get(route('admin.laporan.presensi-santri'))->assertForbidden();
        $this->actingAs($wali)->get(route('admin.laporan.presensi-santri'))->assertForbidden();
        $this->actingAs($calon)->get(route('admin.laporan.presensi-santri'))->assertForbidden();

        $this->actingAs($ustadz)->get(route('admin.laporan.export', [
            'laporan' => 'presensi-santri',
            'format' => 'pdf',
        ]))->assertForbidden();
    }
}
