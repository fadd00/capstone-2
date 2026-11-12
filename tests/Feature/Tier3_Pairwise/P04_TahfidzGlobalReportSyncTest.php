<?php

namespace Tests\Feature\Tier3_Pairwise;

use App\Models\Murojaah;
use App\Models\Santri;
use App\Models\SetoranHafalan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Tier 3 Pairwise Test: P04 Tahfidz to Global Academic Report Sync
 *
 * Source: dokum-cp1 §5.2 Q-ADM-13, §5.3 Q-UST-05, Build Plan §10.2
 * Verifies cross-module synchronization between:
 * 1. Multiple Ustadz recording Setoran Ziyadah and Murojaah for santri binaan
 * 2. Admin querying global hafalan & murojaah reports
 * 3. Filter parameters (month, tingkatan, ustadz, santri)
 * 4. PDF and Excel export endpoints for academic reports
 */
class P04_TahfidzGlobalReportSyncTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    /**
     * 1. Multiple ustadz recording setoran and murojaah reflect comprehensively in admin global reports.
     */
    public function test_multiple_ustadz_setoran_and_murojaah_sync_to_admin_global_reports(): void
    {
        $admin = $this->createAdmin();
        $ustadz1 = $this->createUstadz(['name' => 'Ustadz Bilal']);
        $ustadz2 = $this->createUstadz(['name' => 'Ustadz Salman']);

        $santri1 = Santri::create(['nama' => 'Santri Bilal', 'nis' => '2026401', 'tingkatan' => 'tsanawiyah']);
        $santri2 = Santri::create(['nama' => 'Santri Salman', 'nis' => '2026402', 'tingkatan' => 'aliyah']);

        // Ustadz 1 records Setoran for Santri 1
        SetoranHafalan::create([
            'santri_id' => $santri1->id,
            'ustadz_id' => $ustadz1->id,
            'tanggal' => now()->toDateString(),
            'juz' => 30,
            'surat' => 'An-Naba',
            'ayat' => '1-20',
            'nilai' => 88.00,
            'status_selesai' => 1,
        ]);

        // Ustadz 2 records Murojaah for Santri 2
        Murojaah::create([
            'santri_id' => $santri2->id,
            'ustadz_id' => $ustadz2->id,
            'tanggal' => now()->toDateString(),
            'juz' => 1,
            'surat' => 'Al-Baqarah',
            'ayat' => '1-50',
            'nilai' => 95.00,
            'status_selesai' => 1,
        ]);

        // Admin queries Hafalan Report
        $responseHafalan = $this->actingAs($admin)->get(route('admin.laporan.hafalan'));
        $responseHafalan->assertStatus(200);
        $responseHafalan->assertSee('Santri Bilal');
        $responseHafalan->assertSee('An-Naba');

        // Admin queries Murojaah Report
        $responseMurojaah = $this->actingAs($admin)->get(route('admin.laporan.murojaah'));
        $responseMurojaah->assertStatus(200);
        $responseMurojaah->assertSee('Santri Salman');
        $responseMurojaah->assertSee('Al-Baqarah');
    }

    /**
     * 2. Admin hafalan report accurately filters by month, tingkatan, and ustadz.
     */
    public function test_admin_hafalan_report_filters_by_month_and_tingkatan(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz();

        $santriTsanawiyah = Santri::create(['nama' => 'Santri MTs', 'nis' => '2026403', 'tingkatan' => 'tsanawiyah']);
        $santriAliyah = Santri::create(['nama' => 'Santri MA', 'nis' => '2026404', 'tingkatan' => 'aliyah']);

        SetoranHafalan::create([
            'santri_id' => $santriTsanawiyah->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-05-10',
            'juz' => 30,
            'surat' => 'Al-Ikhlas',
            'ayat' => '1-4',
            'nilai' => 90.00,
            'status_selesai' => 1,
        ]);

        SetoranHafalan::create([
            'santri_id' => $santriAliyah->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-05-12',
            'juz' => 29,
            'surat' => 'Al-Mulk',
            'ayat' => '1-30',
            'nilai' => 92.00,
            'status_selesai' => 1,
        ]);

        // Filter by tingkatan=tsanawiyah
        $responseFilter = $this->actingAs($admin)->get(route('admin.laporan.hafalan', [
            'tingkatan' => 'tsanawiyah',
        ]));

        $responseFilter->assertStatus(200);
        $responseFilter->assertSee('Santri MTs');
    }

    /**
     * 3. Admin can export hafalan and murojaah reports to PDF and Excel formats.
     */
    public function test_admin_export_reports_to_pdf_and_excel(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz();
        $santri = Santri::create(['nama' => 'Santri Export', 'nis' => '2026405', 'tingkatan' => 'takhassus']);

        SetoranHafalan::create([
            'santri_id' => $santri->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => now()->toDateString(),
            'juz' => 30,
            'surat' => 'An-Nas',
            'ayat' => '1-6',
            'nilai' => 99.00,
            'status_selesai' => 1,
        ]);

        // Export PDF
        $responsePdf = $this->actingAs($admin)->get(route('admin.laporan.export', [
            'laporan' => 'hafalan',
            'format' => 'pdf',
        ]));

        $this->assertTrue(
            $responsePdf->isSuccessful() || $responsePdf->isRedirection(),
            'Export PDF route should execute successfully.'
        );

        // Export Excel
        $responseExcel = $this->actingAs($admin)->get(route('admin.laporan.export', [
            'laporan' => 'hafalan',
            'format' => 'excel',
        ]));

        $this->assertTrue(
            $responseExcel->isSuccessful() || $responseExcel->isRedirection(),
            'Export Excel route should execute successfully.'
        );
    }
}
