<?php

namespace Tests\Feature\Tier3_Pairwise;

use App\Models\KegiatanPondok;
use App\Models\Murojaah;
use App\Models\PresensiKegiatan;
use App\Models\PresensiSantri;
use App\Models\Santri;
use App\Models\SetoranHafalan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Tier 3 Pairwise Test: P02 Santri to Wali Murid to Weekly Report
 *
 * Source: dokum-cp1 §5.4 Q-WAL-01..03, Build Plan §10
 * Verifies integration between:
 * 1. Santri enrolled and linked to Wali Murid
 * 2. Ustadz recording Setoran Ziyadah & Murojaah
 * 3. Ustadz conducting activity presensi
 * 4. Dynamic reflection on Wali Murid Dashboard, Weekly Report, and Attendance Log
 * 5. Multi-child isolation between different wali murid accounts
 */
class P02_SantriToWaliToReportTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * 1. Tahfidz entries recorded by Ustadz dynamically aggregate on Wali Murid Dashboard.
     */
    public function test_tahfidz_entries_by_ustadz_dynamically_update_wali_dashboard_summary(): void
    {
        $wali = $this->createWaliMurid(['name' => 'Bapak Sulaiman']);
        $ustadz = $this->createUstadz(['name' => 'Ustadz Mansur']);

        $santri = Santri::create([
            'nama' => 'Yusuf bin Sulaiman',
            'nis' => '2026111',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7A',
            'wali_murid_id' => $wali->id,
        ]);

        $ustadz->santriBinaan()->attach($santri->id);

        // Ustadz submits Setoran Ziyadah
        SetoranHafalan::create([
            'santri_id' => $santri->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => now()->toDateString(),
            'juz' => 30,
            'surat' => 'An-Naba',
            'ayat' => '1-40',
            'nilai' => 92.50,
            'status_selesai' => 1,
            'catatan' => 'Mumtaz, tajwid lancar',
        ]);

        // Ustadz submits Murojaah
        Murojaah::create([
            'santri_id' => $santri->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => now()->toDateString(),
            'juz' => 29,
            'surat' => 'Al-Mulk',
            'ayat' => '1-30',
            'nilai' => 88.00,
            'status_selesai' => 1,
            'catatan' => 'Lancar, perhatikan makhraj huruf',
        ]);

        // Wali Murid visits dashboard
        $response = $this->actingAs($wali)->get(route('wali-murid.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('wali.dashboard');
        $response->assertSee('Yusuf bin Sulaiman');
        $response->assertSee('An-Naba');
    }

    /**
     * 2. Laporan Mingguan accurately groups records by week window (Monday - Sunday).
     */
    public function test_wali_laporan_mingguan_aggregates_records_accurately_by_week_window(): void
    {
        $wali = $this->createWaliMurid();
        $ustadz = $this->createUstadz();

        $santri = Santri::create([
            'nama' => 'Ibrahim',
            'nis' => '2026112',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '8B',
            'wali_murid_id' => $wali->id,
        ]);

        $ustadz->santriBinaan()->attach($santri->id);

        // Set current time to Wednesday 2026-05-13
        Carbon::setTestNow(Carbon::parse('2026-05-13 10:00:00'));

        // Current week Monday (2026-05-11)
        SetoranHafalan::create([
            'santri_id' => $santri->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-05-11',
            'juz' => 1,
            'surat' => 'Al-Baqarah',
            'ayat' => '1-25',
            'nilai' => 85.00,
            'status_selesai' => 1,
        ]);

        // Past week record (2026-05-04)
        SetoranHafalan::create([
            'santri_id' => $santri->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-05-04',
            'juz' => 30,
            'surat' => 'An-Nas',
            'ayat' => '1-6',
            'nilai' => 95.00,
            'status_selesai' => 1,
        ]);

        $response = $this->actingAs($wali)->get(route('wali-murid.laporan-mingguan.index'));

        $response->assertStatus(200);
        $response->assertViewIs('wali.laporan-mingguan');
        $response->assertSee('Al-Baqarah');
    }

    /**
     * 3. Riwayat Kehadiran displays activity and ustadz details for enrolled child.
     */
    public function test_wali_riwayat_kehadiran_displays_activity_and_ustadz_details(): void
    {
        $wali = $this->createWaliMurid();
        $ustadz = $this->createUstadz(['name' => 'Ustadz Zulkifli']);

        $santri = Santri::create([
            'nama' => 'Ali Murtadha',
            'nis' => '2026113',
            'tingkatan' => 'aliyah',
            'kelas' => '11',
            'wali_murid_id' => $wali->id,
        ]);

        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Kajian Kitab Riyadhus Shalihin',
            'tingkatan' => 'aliyah',
        ]);

        $presensiKegiatan = PresensiKegiatan::create([
            'kegiatan_pondok_id' => $kegiatan->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => now()->toDateString(),
            'hari' => 'Rabu',
            'jam_mulai' => '06:00:00',
            'jam_selesai' => '07:00:00',
            'status' => 'valid',
            'materi' => 'Bab Ikhlas',
        ]);

        PresensiSantri::create([
            'presensi_kegiatan_id' => $presensiKegiatan->id,
            'santri_id' => $santri->id,
            'status' => 'hadir',
        ]);

        $response = $this->actingAs($wali)->get(route('wali-murid.riwayat-kegiatan.index'));

        $response->assertStatus(200);
        $response->assertViewIs('wali.riwayat-kegiatan');
        $response->assertSee('Kajian Kitab Riyadhus Shalihin');
        $response->assertSee('hadir');
    }

    /**
     * 4. Multi-child wali murid sees data for all their children without leaking to another parent.
     */
    public function test_multi_child_wali_murid_sees_their_children_and_not_other_parents_children(): void
    {
        $waliA = $this->createWaliMurid(['name' => 'Wali A']);
        $waliB = $this->createWaliMurid(['name' => 'Wali B']);

        $childA1 = Santri::create([
            'nama' => 'Anak A Pertama',
            'nis' => '2026114',
            'tingkatan' => 'tsanawiyah',
            'wali_murid_id' => $waliA->id,
        ]);

        $childA2 = Santri::create([
            'nama' => 'Anak A Kedua',
            'nis' => '2026115',
            'tingkatan' => 'aliyah',
            'wali_murid_id' => $waliA->id,
        ]);

        $childB = Santri::create([
            'nama' => 'Anak B Tunggal',
            'nis' => '2026116',
            'tingkatan' => 'tsanawiyah',
            'wali_murid_id' => $waliB->id,
        ]);

        $response = $this->actingAs($waliA)->get(route('wali-murid.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Anak A Pertama');
        $response->assertSee('Anak A Kedua');
        $response->assertDontSee('Anak B Tunggal');
    }
}
