<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\KegiatanPondok;
use App\Models\PresensiKegiatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 38: Laporan Presensi Ustadz Global (F38)
 *
 * Source: dokum-cp1 §5.2 Q-ADM-11, Build Plan §10.2
 * Minimum 5 test methods covering:
 * - Viewing global ustadz presensi report
 * - Filtering by ustadz and kegiatan
 * - Filtering by month / date range
 * - Exporting report to PDF
 * - Exporting report to Excel
 * - Role-based authorization guard (403 for non-admin)
 */
class F38_LaporanPresensiUstadzTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    public function test_admin_can_view_presensi_ustadz_report_page(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz(['name' => 'Ustadz Laporan Test']);
        $kegiatan = KegiatanPondok::create(['nama_kegiatan' => 'Tahfidz Al-Qur\'an', 'tingkatan' => 'semua']);

        PresensiKegiatan::create([
            'kegiatan_pondok_id' => $kegiatan->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-12',
            'hari' => 'Kamis',
            'jam_mulai' => '05:30:00',
            'latitude' => -7.797068,
            'longitude' => 110.370529,
            'status' => 'valid',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.laporan.presensi-ustadz'));

        $response->assertOk();
        $response->assertSee('Ustadz Laporan Test');
        $response->assertSee('Tahfidz Al-Qur\'an');
    }

    public function test_admin_can_filter_report_by_ustadz_and_activity(): void
    {
        $admin = $this->createAdmin();
        $ustadz1 = $this->createUstadz(['name' => 'Ustadz Alpha']);
        $ustadz2 = $this->createUstadz(['name' => 'Ustadz Beta']);

        $kegiatan1 = KegiatanPondok::create(['nama_kegiatan' => 'Kegiatan Alpha', 'tingkatan' => 'semua']);
        $kegiatan2 = KegiatanPondok::create(['nama_kegiatan' => 'Kegiatan Beta', 'tingkatan' => 'semua']);

        PresensiKegiatan::create([
            'kegiatan_pondok_id' => $kegiatan1->id,
            'ustadz_id' => $ustadz1->id,
            'tanggal' => '2026-11-12',
            'hari' => 'Kamis',
            'jam_mulai' => '05:30:00',
            'status' => 'valid',
        ]);
        PresensiKegiatan::create([
            'kegiatan_pondok_id' => $kegiatan2->id,
            'ustadz_id' => $ustadz2->id,
            'tanggal' => '2026-11-12',
            'hari' => 'Kamis',
            'jam_mulai' => '06:30:00',
            'status' => 'valid',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.laporan.presensi-ustadz', [
            'ustadz_id' => $ustadz1->id,
            'kegiatan_pondok_id' => $kegiatan1->id,
        ]));

        $response->assertOk();
        $response->assertSee('Ustadz Alpha');
        $response->assertDontSee('Ustadz Beta');
    }

    public function test_admin_can_filter_report_by_month(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz();
        $kegiatan = KegiatanPondok::create(['nama_kegiatan' => 'Tahfidz Rutin', 'tingkatan' => 'semua']);

        PresensiKegiatan::create([
            'kegiatan_pondok_id' => $kegiatan->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-05',
            'hari' => 'Kamis',
            'jam_mulai' => '05:30:00',
            'status' => 'valid',
        ]);
        PresensiKegiatan::create([
            'kegiatan_pondok_id' => $kegiatan->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-10-15',
            'hari' => 'Kamis',
            'jam_mulai' => '05:30:00',
            'status' => 'valid',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.laporan.presensi-ustadz', [
            'bulan' => '2026-11',
        ]));

        $response->assertOk();
        $response->assertSee('2026-11-05');
        $response->assertDontSee('2026-10-15');
    }

    public function test_admin_can_export_presensi_ustadz_report_to_pdf(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.laporan.export', [
            'laporan' => 'presensi-ustadz',
            'format' => 'pdf',
        ]));

        $this->assertTrue(
            $response->status() === 200 || $response->isRedirection(),
            'Expected successful PDF export trigger'
        );
    }

    public function test_admin_can_export_presensi_ustadz_report_to_excel(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.laporan.export', [
            'laporan' => 'presensi-ustadz',
            'format' => 'excel',
        ]));

        $this->assertTrue(
            $response->status() === 200 || $response->isRedirection(),
            'Expected successful Excel export trigger'
        );
    }

    public function test_non_admin_cannot_access_or_export_presensi_ustadz_report(): void
    {
        $ustadz = $this->createUstadz();
        $wali = $this->createWaliMurid();
        $calon = $this->createCalonSantri();

        $this->actingAs($ustadz)->get(route('admin.laporan.presensi-ustadz'))->assertForbidden();
        $this->actingAs($wali)->get(route('admin.laporan.presensi-ustadz'))->assertForbidden();
        $this->actingAs($calon)->get(route('admin.laporan.presensi-ustadz'))->assertForbidden();

        $this->actingAs($ustadz)->get(route('admin.laporan.export', [
            'laporan' => 'presensi-ustadz',
            'format' => 'pdf',
        ]))->assertForbidden();
    }
}
