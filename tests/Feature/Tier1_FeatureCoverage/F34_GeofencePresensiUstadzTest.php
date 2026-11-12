<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\KegiatanPondok;
use App\Models\PresensiKegiatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 34: Geolocation Presensi Ustadz (F34)
 *
 * Source: dokum-cp1 §6, §5.3 Q-UST-02, Build Plan §8.1
 * Coordinates Center: -7.797068, 110.370529. Threshold: 150.0 meters.
 * Minimum 5 test methods covering:
 * - Viewing ustadz presensi index and create form
 * - Check-in within 150m radius records status 'valid'
 * - Check-in beyond 150m radius records status 'invalid'
 * - Double check-in prevention on same activity and date
 * - Forbidden check-in to unassigned activity (403)
 * - Role-based authorization guard (403 for non-ustadz)
 */
class F34_GeofencePresensiUstadzTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    protected const PESANTREN_LAT = -7.797068;

    protected const PESANTREN_LON = 110.370529;

    public function test_ustadz_can_view_presensi_kegiatan_index_and_create_form(): void
    {
        $ustadz = $this->createUstadz();
        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Tahfidz Al-Qur\'an Intensif',
            'tingkatan' => 'semua',
        ]);
        $ustadz->kegiatanPondok()->attach($kegiatan->id);

        $responseIndex = $this->actingAs($ustadz)->get(route('ustadz.presensi-kegiatan.index'));
        $responseIndex->assertOk();

        $responseCreate = $this->actingAs($ustadz)->get(route('ustadz.presensi-kegiatan.create'));
        $responseCreate->assertOk();
    }

    public function test_checkin_within_150m_radius_sets_status_valid(): void
    {
        $ustadz = $this->createUstadz();
        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Tahfidz Pagi',
            'tingkatan' => 'tsanawiyah',
        ]);
        $ustadz->kegiatanPondok()->attach($kegiatan->id);

        // Near center: ~5-10 meters offset
        $payload = [
            'kegiatan_pondok_id' => $kegiatan->id,
            'tanggal' => '2026-11-12',
            'hari' => 'Kamis',
            'jam_mulai' => '05:30:00',
            'jam_selesai' => '07:00:00',
            'materi' => 'Setoran Surat Al-Mulk ayat 1-15',
            'latitude' => self::PESANTREN_LAT + 0.000050,
            'longitude' => self::PESANTREN_LON + 0.000050,
        ];

        $response = $this->actingAs($ustadz)->post(route('ustadz.presensi-kegiatan.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('presensi_kegiatan', [
            'ustadz_id' => $ustadz->id,
            'kegiatan_pondok_id' => $kegiatan->id,
            'tanggal' => '2026-11-12',
            'status' => 'valid',
        ]);
    }

    public function test_checkin_beyond_150m_radius_sets_status_invalid(): void
    {
        $ustadz = $this->createUstadz();
        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Kajian Fiqih',
            'tingkatan' => 'aliyah',
        ]);
        $ustadz->kegiatanPondok()->attach($kegiatan->id);

        // Far from pesantren: ~800+ meters away
        $payload = [
            'kegiatan_pondok_id' => $kegiatan->id,
            'tanggal' => '2026-11-12',
            'hari' => 'Kamis',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '09:30:00',
            'materi' => 'Bab Taharah Fiqih Syafi\'i',
            'latitude' => self::PESANTREN_LAT + 0.008000,
            'longitude' => self::PESANTREN_LON + 0.008000,
        ];

        $response = $this->actingAs($ustadz)->post(route('ustadz.presensi-kegiatan.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('presensi_kegiatan', [
            'ustadz_id' => $ustadz->id,
            'kegiatan_pondok_id' => $kegiatan->id,
            'tanggal' => '2026-11-12',
            'status' => 'invalid',
        ]);
    }

    public function test_ustadz_cannot_checkin_twice_for_same_activity_on_same_date(): void
    {
        $ustadz = $this->createUstadz();
        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Tahfidz Sore',
            'tingkatan' => 'semua',
        ]);
        $ustadz->kegiatanPondok()->attach($kegiatan->id);

        PresensiKegiatan::create([
            'kegiatan_pondok_id' => $kegiatan->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-12',
            'hari' => 'Kamis',
            'jam_mulai' => '16:00:00',
            'latitude' => self::PESANTREN_LAT,
            'longitude' => self::PESANTREN_LON,
            'status' => 'valid',
        ]);

        $secondPayload = [
            'kegiatan_pondok_id' => $kegiatan->id,
            'tanggal' => '2026-11-12',
            'hari' => 'Kamis',
            'jam_mulai' => '16:30:00',
            'latitude' => self::PESANTREN_LAT,
            'longitude' => self::PESANTREN_LON,
        ];

        $response = $this->actingAs($ustadz)->post(route('ustadz.presensi-kegiatan.store'), $secondPayload);

        $this->assertTrue(
            $response->isClientError() || $response->isRedirection(),
            'Expected second check-in on same activity and date to be rejected'
        );

        $this->assertCount(1, PresensiKegiatan::where([
            'kegiatan_pondok_id' => $kegiatan->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-12',
        ])->get());
    }

    public function test_ustadz_cannot_checkin_to_unassigned_activity(): void
    {
        $ustadz = $this->createUstadz();
        $unassignedKegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Kajian Kitab Kuning Bukan Tugasnya',
            'tingkatan' => 'aliyah',
        ]);

        $payload = [
            'kegiatan_pondok_id' => $unassignedKegiatan->id,
            'tanggal' => '2026-11-12',
            'hari' => 'Kamis',
            'jam_mulai' => '10:00:00',
            'latitude' => self::PESANTREN_LAT,
            'longitude' => self::PESANTREN_LON,
        ];

        $response = $this->actingAs($ustadz)->post(route('ustadz.presensi-kegiatan.store'), $payload);

        $this->assertTrue(
            $response->status() === 403 || $response->isClientError() || $response->isRedirection(),
            'Expected unauthorized assignment access'
        );
        $this->assertDatabaseMissing('presensi_kegiatan', [
            'ustadz_id' => $ustadz->id,
            'kegiatan_pondok_id' => $unassignedKegiatan->id,
        ]);
    }

    public function test_non_ustadz_cannot_perform_ustadz_presensi(): void
    {
        $wali = $this->createWaliMurid();
        $calon = $this->createCalonSantri();

        $this->actingAs($wali)->get(route('ustadz.presensi-kegiatan.index'))->assertForbidden();
        $this->actingAs($calon)->get(route('ustadz.presensi-kegiatan.index'))->assertForbidden();

        $this->actingAs($wali)->post(route('ustadz.presensi-kegiatan.store'), [
            'kegiatan_pondok_id' => 1,
            'tanggal' => '2026-11-12',
        ])->assertForbidden();
    }
}
