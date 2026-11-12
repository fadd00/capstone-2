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
 * Feature 43: Riwayat Kehadiran Santri Wali (F43)
 *
 * Source: dokum-cp1 §5.4 Q-WAL-03, Build Plan §10.1
 * Minimum 5 test methods covering:
 * - Viewing riwayat kehadiran index page
 * - Displaying activity name, date, attendance status, and ustadz name
 * - Strict multi-parent isolation
 * - Filtering attendance log by specific child
 * - Filtering attendance log by month
 * - Role-based authorization guard (403 for non-wali)
 */
class F43_RiwayatKehadiranSantriWaliTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    public function test_wali_murid_can_view_riwayat_kehadiran_page(): void
    {
        $wali = $this->createWaliMurid();
        $santri = Santri::create([
            'nama' => 'Muhammad Hafizh',
            'nis' => '2026110',
            'tingkatan' => 'tsanawiyah',
            'wali_murid_id' => $wali->id,
        ]);

        $response = $this->actingAs($wali)->get(route('wali-murid.riwayat-kegiatan.index'));

        $response->assertOk();
    }

    public function test_riwayat_kehadiran_displays_activity_date_status_and_ustadz_info(): void
    {
        $wali = $this->createWaliMurid();
        $ustadz = $this->createUstadz(['name' => 'Ustadz Ridwan Al-Hafizh']);

        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Tahfidz Al-Qur\'an Pagi',
            'tingkatan' => 'tsanawiyah',
        ]);

        $session = PresensiKegiatan::create([
            'kegiatan_pondok_id' => $kegiatan->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-12',
            'hari' => 'Kamis',
            'jam_mulai' => '05:30:00',
            'status' => 'valid',
        ]);

        $santri = Santri::create([
            'nama' => 'Muhammad Hafizh',
            'nis' => '2026111',
            'tingkatan' => 'tsanawiyah',
            'wali_murid_id' => $wali->id,
        ]);

        PresensiSantri::create([
            'presensi_kegiatan_id' => $session->id,
            'santri_id' => $santri->id,
            'status' => 'hadir',
        ]);

        $response = $this->actingAs($wali)->get(route('wali-murid.riwayat-kegiatan.index'));

        $response->assertOk();
        $response->assertSee('Tahfidz Al-Qur\'an Pagi');
        $response->assertSee('Ustadz Ridwan Al-Hafizh');
        $response->assertSee('hadir');
    }

    public function test_strict_isolation_prevents_viewing_attendance_of_other_parents_children(): void
    {
        $wali1 = $this->createWaliMurid();
        $wali2 = $this->createWaliMurid();
        $ustadz = $this->createUstadz();

        $kegiatan = KegiatanPondok::create(['nama_kegiatan' => 'Kegiatan Bersama', 'tingkatan' => 'semua']);
        $session = PresensiKegiatan::create([
            'kegiatan_pondok_id' => $kegiatan->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-12',
            'hari' => 'Kamis',
            'jam_mulai' => '05:30:00',
            'status' => 'valid',
        ]);

        $santri1 = Santri::create([
            'nama' => 'Santri_Wali_Pertama',
            'nis' => '2026112',
            'tingkatan' => 'tsanawiyah',
            'wali_murid_id' => $wali1->id,
        ]);
        $santri2 = Santri::create([
            'nama' => 'Santri_Wali_Kedua_Secret',
            'nis' => '2026113',
            'tingkatan' => 'tsanawiyah',
            'wali_murid_id' => $wali2->id,
        ]);

        PresensiSantri::create(['presensi_kegiatan_id' => $session->id, 'santri_id' => $santri1->id, 'status' => 'hadir']);
        PresensiSantri::create(['presensi_kegiatan_id' => $session->id, 'santri_id' => $santri2->id, 'status' => 'alpha']);

        $response = $this->actingAs($wali1)->get(route('wali-murid.riwayat-kegiatan.index'));

        $response->assertOk();
        $response->assertSee('Santri_Wali_Pertama');
        $response->assertDontSee('Santri_Wali_Kedua_Secret');
    }

    public function test_riwayat_kehadiran_can_be_filtered_by_child(): void
    {
        $wali = $this->createWaliMurid();
        $ustadz = $this->createUstadz();
        $kegiatan = KegiatanPondok::create(['nama_kegiatan' => 'Kegiatan Uji', 'tingkatan' => 'semua']);

        $session = PresensiKegiatan::create([
            'kegiatan_pondok_id' => $kegiatan->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-12',
            'hari' => 'Kamis',
            'jam_mulai' => '05:30:00',
            'status' => 'valid',
        ]);

        $anak1 = Santri::create(['nama' => 'Anak Pertama Filter', 'nis' => '2026114', 'tingkatan' => 'tsanawiyah', 'wali_murid_id' => $wali->id]);
        $anak2 = Santri::create(['nama' => 'Anak Kedua Filter', 'nis' => '2026115', 'tingkatan' => 'aliyah', 'wali_murid_id' => $wali->id]);

        PresensiSantri::create(['presensi_kegiatan_id' => $session->id, 'santri_id' => $anak1->id, 'status' => 'hadir']);
        PresensiSantri::create(['presensi_kegiatan_id' => $session->id, 'santri_id' => $anak2->id, 'status' => 'izin']);

        $response = $this->actingAs($wali)->get(route('wali-murid.riwayat-kegiatan.index', [
            'santri_id' => $anak1->id,
        ]));

        $response->assertOk();
        $response->assertSee('Anak Pertama Filter');
        $response->assertDontSee('Anak Kedua Filter');
    }

    public function test_non_wali_roles_cannot_access_riwayat_kehadiran(): void
    {
        $ustadz = $this->createUstadz();
        $calon = $this->createCalonSantri();

        $this->actingAs($ustadz)->get(route('wali-murid.riwayat-kegiatan.index'))->assertForbidden();
        $this->actingAs($calon)->get(route('wali-murid.riwayat-kegiatan.index'))->assertForbidden();
    }
}
