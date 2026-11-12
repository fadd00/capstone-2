<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\Murojaah;
use App\Models\Santri;
use App\Models\SetoranHafalan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 40: Laporan Setoran & Murojaah Global (F40)
 *
 * Source: dokum-cp1 §5.2 Q-ADM-13, Build Plan §10.2
 * Minimum 5 test methods covering:
 * - Viewing global setoran hafalan report
 * - Filtering setoran hafalan by santri and ustadz
 * - Viewing global murojaah report and filtering by month
 * - Exporting setoran report to PDF and Excel
 * - Exporting murojaah report to PDF and Excel
 * - Role-based authorization guard (403 for non-admin)
 */
class F40_LaporanSetoranMurojaahTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    public function test_admin_can_view_laporan_hafalan_page_and_see_records(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz();
        $santri = Santri::create(['nama' => 'Santri Tahfidz', 'nis' => '2026080', 'tingkatan' => 'tsanawiyah']);

        SetoranHafalan::create([
            'santri_id' => $santri->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-12',
            'juz' => 30,
            'surat' => 'An-Naba',
            'ayat' => '1-40',
            'nilai' => 95.00,
            'status_selesai' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.laporan.hafalan'));

        $response->assertOk();
        $response->assertSee('Santri Tahfidz');
        $response->assertSee('An-Naba');
    }

    public function test_admin_can_filter_laporan_hafalan_by_santri_and_ustadz(): void
    {
        $admin = $this->createAdmin();
        $ustadz1 = $this->createUstadz(['name' => 'Ustadz Pembina 1']);
        $ustadz2 = $this->createUstadz(['name' => 'Ustadz Pembina 2']);

        $santri1 = Santri::create(['nama' => 'Santri Target', 'nis' => '2026081', 'tingkatan' => 'tsanawiyah']);
        $santri2 = Santri::create(['nama' => 'Santri Lain', 'nis' => '2026082', 'tingkatan' => 'aliyah']);

        SetoranHafalan::create([
            'santri_id' => $santri1->id,
            'ustadz_id' => $ustadz1->id,
            'tanggal' => '2026-11-12',
            'juz' => 30,
            'surat' => 'An-Nazi\'at',
            'ayat' => '1-46',
            'nilai' => 90.00,
            'status_selesai' => 1,
        ]);
        SetoranHafalan::create([
            'santri_id' => $santri2->id,
            'ustadz_id' => $ustadz2->id,
            'tanggal' => '2026-11-12',
            'juz' => 1,
            'surat' => 'Al-Baqarah',
            'ayat' => '1-20',
            'nilai' => 88.00,
            'status_selesai' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.laporan.hafalan', [
            'santri_id' => $santri1->id,
            'ustadz_id' => $ustadz1->id,
        ]));

        $response->assertOk();
        $response->assertSee('Santri Target');
        $response->assertDontSee('Santri Lain');
    }

    public function test_admin_can_view_laporan_murojaah_page_and_filter_by_month(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz();
        $santri = Santri::create(['nama' => 'Santri Murojaah', 'nis' => '2026083', 'tingkatan' => 'tsanawiyah']);

        Murojaah::create([
            'santri_id' => $santri->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-05',
            'juz' => 29,
            'surat' => 'Al-Mulk',
            'ayat' => '1-30',
            'nilai' => 96.00,
            'status_selesai' => 1,
        ]);
        Murojaah::create([
            'santri_id' => $santri->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-10-10',
            'juz' => 28,
            'surat' => 'Al-Mujadilah',
            'ayat' => '1-22',
            'nilai' => 91.00,
            'status_selesai' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.laporan.murojaah', [
            'bulan' => '2026-11',
        ]));

        $response->assertOk();
        $response->assertSee('Al-Mulk');
        $response->assertDontSee('Al-Mujadilah');
    }

    public function test_admin_can_export_laporan_hafalan_to_pdf_and_excel(): void
    {
        $admin = $this->createAdmin();

        $responsePdf = $this->actingAs($admin)->get(route('admin.laporan.export', [
            'laporan' => 'hafalan',
            'format' => 'pdf',
        ]));
        $this->assertTrue($responsePdf->status() === 200 || $responsePdf->isRedirection());

        $responseExcel = $this->actingAs($admin)->get(route('admin.laporan.export', [
            'laporan' => 'hafalan',
            'format' => 'excel',
        ]));
        $this->assertTrue($responseExcel->status() === 200 || $responseExcel->isRedirection());
    }

    public function test_admin_can_export_laporan_murojaah_to_pdf_and_excel(): void
    {
        $admin = $this->createAdmin();

        $responsePdf = $this->actingAs($admin)->get(route('admin.laporan.export', [
            'laporan' => 'murojaah',
            'format' => 'pdf',
        ]));
        $this->assertTrue($responsePdf->status() === 200 || $responsePdf->isRedirection());

        $responseExcel = $this->actingAs($admin)->get(route('admin.laporan.export', [
            'laporan' => 'murojaah',
            'format' => 'excel',
        ]));
        $this->assertTrue($responseExcel->status() === 200 || $responseExcel->isRedirection());
    }

    public function test_non_admin_cannot_access_or_export_tahfidz_reports(): void
    {
        $ustadz = $this->createUstadz();
        $wali = $this->createWaliMurid();
        $calon = $this->createCalonSantri();

        $this->actingAs($ustadz)->get(route('admin.laporan.hafalan'))->assertForbidden();
        $this->actingAs($wali)->get(route('admin.laporan.murojaah'))->assertForbidden();
        $this->actingAs($calon)->get(route('admin.laporan.hafalan'))->assertForbidden();

        $this->actingAs($ustadz)->get(route('admin.laporan.export', [
            'laporan' => 'hafalan',
            'format' => 'pdf',
        ]))->assertForbidden();
    }
}
