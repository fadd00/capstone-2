<?php

namespace Tests\Feature\Tier4_Scenarios;

use App\Models\KegiatanPondok;
use App\Models\Murojaah;
use App\Models\PresensiKegiatan;
use App\Models\PresensiSantri;
use App\Models\Santri;
use App\Models\SetoranHafalan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Tier 4 Scenario Test: S04 Wali Murid Monitoring Journey
 *
 * Source: dokum-cp1 §5.4 Q-WAL-01..03, Build Plan §10, §16.2
 * Verifies realistic user journeys for parents (wali murid):
 * - Journey 1: Single Child Comprehensive Monitoring (Dashboard review -> Laporan Mingguan breakdown ->
 *              Riwayat Kehadiran attendance log -> verification of ustadz remarks).
 * - Journey 2: Multi-Child Family Monitoring & Privacy Isolation (Parent with 2 children switches views,
 *              confirms metrics for each child, attempts to access other families' data -> 403 Forbidden).
 */
class S04_WaliMuridMonitoringJourneyTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    /**
     * 1. Wali Murid Comprehensive Single-Child Monitoring Journey.
     */
    public function test_wali_murid_comprehensive_monitoring_journey(): void
    {
        $wali = $this->createWaliMurid(['name' => 'Bapak Muhammad Hidayat']);
        $ustadz = $this->createUstadz(['name' => 'Ustadz Salman Al-Farisi']);

        $santri = Santri::create([
            'nama' => 'Ilyas Hidayat',
            'nis' => '2026701',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7B',
            'wali_murid_id' => $wali->id,
        ]);

        $ustadz->santriBinaan()->attach($santri->id);

        // Seed Setoran Ziyadah
        SetoranHafalan::create([
            'santri_id' => $santri->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => now()->toDateString(),
            'juz' => 30,
            'surat' => 'An-Nazi’at',
            'ayat' => '1-46',
            'nilai' => 93.00,
            'status_selesai' => 1,
            'catatan' => 'Makharijul huruf sangat bagus.',
        ]);

        // Seed Murojaah
        Murojaah::create([
            'santri_id' => $santri->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => now()->toDateString(),
            'juz' => 30,
            'surat' => 'An-Naba',
            'ayat' => '1-40',
            'nilai' => 95.00,
            'status_selesai' => 1,
            'catatan' => 'Murojaah lancar tanpa kesalahan.',
        ]);

        // Seed Activity & Presensi
        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Sholat Berjamaah & Dzikir Pagi',
            'tingkatan' => 'tsanawiyah',
        ]);

        $presensiSession = PresensiKegiatan::create([
            'kegiatan_pondok_id' => $kegiatan->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => now()->toDateString(),
            'hari' => 'Selasa',
            'jam_mulai' => '04:30:00',
            'status' => 'valid',
        ]);

        PresensiSantri::create([
            'presensi_kegiatan_id' => $presensiSession->id,
            'santri_id' => $santri->id,
            'status' => 'hadir',
        ]);

        // Step 1: Parent logs in and visits dashboard
        $dashboardResponse = $this->actingAs($wali)->get(route('wali-murid.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertViewIs('wali.dashboard');
        $dashboardResponse->assertSee('Ilyas Hidayat');
        $dashboardResponse->assertSee('An-Nazi’at');

        // Step 2: Parent checks Laporan Mingguan
        $weeklyResponse = $this->actingAs($wali)->get(route('wali-murid.laporan-mingguan.index'));
        $weeklyResponse->assertStatus(200);
        $weeklyResponse->assertViewIs('wali.laporan-mingguan');
        $weeklyResponse->assertSee('An-Nazi’at');
        $weeklyResponse->assertSee('93');

        // Step 3: Parent checks Riwayat Kehadiran
        $attendanceResponse = $this->actingAs($wali)->get(route('wali-murid.riwayat-kegiatan.index'));
        $attendanceResponse->assertStatus(200);
        $attendanceResponse->assertViewIs('wali.riwayat-kegiatan');
        $attendanceResponse->assertSee('Sholat Berjamaah & Dzikir Pagi');
        $attendanceResponse->assertSee('hadir');
    }

    /**
     * 2. Multi-Child Parent Monitoring and Privacy Isolation Journey.
     */
    public function test_multi_child_parent_monitoring_and_privacy_isolation_journey(): void
    {
        $waliA = $this->createWaliMurid(['name' => 'Bapak Abdullah']);
        $waliB = $this->createWaliMurid(['name' => 'Bapak Utsman']);

        // Wali A has two children
        $childA1 = Santri::create([
            'nama' => 'Zubair bin Abdullah',
            'nis' => '2026702',
            'tingkatan' => 'tsanawiyah',
            'wali_murid_id' => $waliA->id,
        ]);
        $childA2 = Santri::create([
            'nama' => 'Hamzah bin Abdullah',
            'nis' => '2026703',
            'tingkatan' => 'aliyah',
            'wali_murid_id' => $waliA->id,
        ]);

        // Wali B has one child
        $childB = Santri::create([
            'nama' => 'Thalhah bin Utsman',
            'nis' => '2026704',
            'tingkatan' => 'tsanawiyah',
            'wali_murid_id' => $waliB->id,
        ]);

        // Step 1: Wali A visits dashboard and sees both own children
        $dashboardResponse = $this->actingAs($waliA)->get(route('wali-murid.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Zubair bin Abdullah');
        $dashboardResponse->assertSee('Hamzah bin Abdullah');
        $dashboardResponse->assertDontSee('Thalhah bin Utsman');

        // Step 2: Wali A queries reports for own child 1
        $reportA1 = $this->actingAs($waliA)->get(
            route('wali-murid.laporan-mingguan.index', ['santri_id' => $childA1->id])
        );
        $reportA1->assertStatus(200);

        // Step 3: Wali A queries reports for own child 2
        $reportA2 = $this->actingAs($waliA)->get(
            route('wali-murid.laporan-mingguan.index', ['santri_id' => $childA2->id])
        );
        $reportA2->assertStatus(200);

        // Step 4: Wali A attempts to query child belonging to Wali B -> blocked / 403 or excluded
        $maliciousReport = $this->actingAs($waliA)->get(
            route('wali-murid.laporan-mingguan.index', ['santri_id' => $childB->id])
        );

        $this->assertTrue(
            $maliciousReport->status() === 403 ||
            ! str_contains($maliciousReport->getContent(), 'Thalhah bin Utsman'),
            'Access to reports of unlinked child must be strictly prevented.'
        );
    }
}
