<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\Murojaah;
use App\Models\Santri;
use App\Models\SetoranHafalan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 42: Laporan Mingguan Wali Murid (F42)
 *
 * Source: dokum-cp1 §5.4 Q-WAL-02, Build Plan §10.1
 * Minimum 5 test methods covering:
 * - Viewing weekly report page
 * - Weekly aggregation (Monday to Sunday) of setoran and murojaah
 * - Displaying ustadz feedback notes and teacher identity
 * - Strict multi-parent isolation
 * - Filtering by week / date range
 * - Role-based authorization guard (403 for non-wali)
 */
class F42_LaporanMingguanWaliTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-11 12:00:00', 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_wali_murid_can_view_laporan_mingguan_index_page(): void
    {
        $wali = $this->createWaliMurid();
        $santri = Santri::create([
            'nama' => 'Muhammad Hafizh',
            'nis' => '2026100',
            'tingkatan' => 'tsanawiyah',
            'wali_murid_id' => $wali->id,
        ]);

        $response = $this->actingAs($wali)->get(route('wali-murid.laporan-mingguan.index'));

        $response->assertOk();
    }

    public function test_laporan_mingguan_displays_weekly_breakdown_of_setoran_and_murojaah(): void
    {
        $wali = $this->createWaliMurid();
        $ustadz = $this->createUstadz(['name' => 'Ustadz Ahmad Faqih']);
        $santri = Santri::create([
            'nama' => 'Muhammad Hafizh',
            'nis' => '2026101',
            'tingkatan' => 'tsanawiyah',
            'wali_murid_id' => $wali->id,
        ]);

        SetoranHafalan::create([
            'santri_id' => $santri->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-10', // Tuesday
            'juz' => 30,
            'surat' => 'An-Naba',
            'ayat' => '1-40',
            'nilai' => 94.00,
            'status_selesai' => 1,
            'catatan' => 'Sangat lancar dan fasih',
        ]);

        Murojaah::create([
            'santri_id' => $santri->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-11', // Wednesday
            'juz' => 29,
            'surat' => 'Al-Mulk',
            'ayat' => '1-30',
            'nilai' => 92.00,
            'status_selesai' => 1,
        ]);

        $response = $this->actingAs($wali)->get(route('wali-murid.laporan-mingguan.index'));

        $response->assertOk();
        $response->assertSee('An-Naba');
        $response->assertSee('Al-Mulk');
        $response->assertSee('Ustadz Ahmad Faqih');
        $response->assertSee('Sangat lancar dan fasih');
    }

    public function test_strict_isolation_prevents_viewing_weekly_reports_of_other_parents_children(): void
    {
        $wali1 = $this->createWaliMurid();
        $wali2 = $this->createWaliMurid();
        $ustadz = $this->createUstadz();

        $santri1 = Santri::create([
            'nama' => 'Anak Sendiri',
            'nis' => '2026102',
            'tingkatan' => 'tsanawiyah',
            'wali_murid_id' => $wali1->id,
        ]);
        $santri2 = Santri::create([
            'nama' => 'Anak Orang Lain',
            'nis' => '2026103',
            'tingkatan' => 'tsanawiyah',
            'wali_murid_id' => $wali2->id,
        ]);

        SetoranHafalan::create([
            'santri_id' => $santri1->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-10',
            'juz' => 30,
            'surat' => 'Surat_Anak_Sendiri',
            'ayat' => '1-10',
            'nilai' => 90,
            'status_selesai' => 1,
        ]);
        SetoranHafalan::create([
            'santri_id' => $santri2->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-10',
            'juz' => 30,
            'surat' => 'Surat_Anak_Lain_Rahasia',
            'ayat' => '1-10',
            'nilai' => 90,
            'status_selesai' => 1,
        ]);

        $response = $this->actingAs($wali1)->get(route('wali-murid.laporan-mingguan.index'));

        $response->assertOk();
        $response->assertSee('Surat_Anak_Sendiri');
        $response->assertDontSee('Surat_Anak_Lain_Rahasia');
    }

    public function test_laporan_mingguan_can_be_filtered_by_week_or_date(): void
    {
        $wali = $this->createWaliMurid();
        $ustadz = $this->createUstadz();
        $santri = Santri::create([
            'nama' => 'Santri Filter Minggu',
            'nis' => '2026104',
            'tingkatan' => 'tsanawiyah',
            'wali_murid_id' => $wali->id,
        ]);

        // Week 1 record
        SetoranHafalan::create([
            'santri_id' => $santri->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-03',
            'juz' => 30,
            'surat' => 'Surat_Pekan_Lalu',
            'ayat' => '1-10',
            'nilai' => 90,
            'status_selesai' => 1,
        ]);

        // Week 2 record
        SetoranHafalan::create([
            'santri_id' => $santri->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-12',
            'juz' => 30,
            'surat' => 'Surat_Pekan_Ini',
            'ayat' => '1-10',
            'nilai' => 95,
            'status_selesai' => 1,
        ]);

        $response = $this->actingAs($wali)->get(route('wali-murid.laporan-mingguan.index', [
            'tanggal_mulai' => '2026-11-09',
            'tanggal_selesai' => '2026-11-15',
        ]));

        $response->assertOk();
        $response->assertSee('Surat_Pekan_Ini');
        $response->assertDontSee('Surat_Pekan_Lalu');
    }

    public function test_non_wali_roles_cannot_access_laporan_mingguan(): void
    {
        $ustadz = $this->createUstadz();
        $calon = $this->createCalonSantri();

        $this->actingAs($ustadz)->get(route('wali-murid.laporan-mingguan.index'))->assertForbidden();
        $this->actingAs($calon)->get(route('wali-murid.laporan-mingguan.index'))->assertForbidden();
    }
}
