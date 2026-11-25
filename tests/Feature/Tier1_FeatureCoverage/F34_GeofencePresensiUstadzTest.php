<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\KegiatanPondok;
use App\Models\PresensiKegiatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

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
        $responseCreate->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('capture="environment"', false)
            ->assertSee('readonly', false)
            ->assertDontSee('name="tanggal"', false)
            ->assertDontSee('name="hari"', false)
            ->assertDontSee('name="jam_mulai"', false);
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
        $today = now()->toDateString();
        $payload = $this->checkInPayload($kegiatan->id, [
            'jam_selesai' => '07:00',
            'materi' => 'Setoran Surat Al-Mulk ayat 1-15',
            'latitude' => self::PESANTREN_LAT + 0.000050,
            'longitude' => self::PESANTREN_LON + 0.000050,
        ]);

        $response = $this->actingAs($ustadz)->post(route('ustadz.presensi-kegiatan.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('presensi_kegiatan', [
            'ustadz_id' => $ustadz->id,
            'kegiatan_pondok_id' => $kegiatan->id,
            'tanggal' => $today,
            'status' => 'valid',
        ]);

        $session = PresensiKegiatan::where('ustadz_id', $ustadz->id)->firstOrFail();
        Storage::disk('local')->assertExists($session->foto_bukti);
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
        $today = now()->toDateString();
        $payload = $this->checkInPayload($kegiatan->id, [
            'jam_selesai' => '09:30',
            'materi' => 'Bab Taharah Fiqih Syafi\'i',
            'latitude' => self::PESANTREN_LAT + 0.008000,
            'longitude' => self::PESANTREN_LON + 0.008000,
        ]);

        $response = $this->actingAs($ustadz)->post(route('ustadz.presensi-kegiatan.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('presensi_kegiatan', [
            'ustadz_id' => $ustadz->id,
            'kegiatan_pondok_id' => $kegiatan->id,
            'tanggal' => $today,
            'status' => 'invalid',
        ]);
    }

    public function test_checkin_uses_server_timestamp_and_indonesian_weekday_instead_of_client_values(): void
    {
        $ustadz = $this->createUstadz();
        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Kajian Server Time',
            'tingkatan' => 'semua',
        ]);
        $ustadz->kegiatanPondok()->attach($kegiatan->id);

        $checkInTime = \Carbon\Carbon::parse('2026-11-25 12:34:56', 'Asia/Jakarta');
        $this->travelTo($checkInTime);

        $response = $this->actingAs($ustadz)->post(route('ustadz.presensi-kegiatan.store'), $this->checkInPayload($kegiatan->id, [
            'tanggal' => '2000-01-01',
            'hari' => 'Senin',
            'jam_mulai' => '00:01',
        ]));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('presensi_kegiatan', [
            'ustadz_id' => $ustadz->id,
            'kegiatan_pondok_id' => $kegiatan->id,
            'tanggal' => '2026-11-25',
            'hari' => 'Rabu',
            'jam_mulai' => '12:34:56',
        ]);

        $this->travelBack();
    }

    public function test_checkin_requires_an_evidence_photo(): void
    {
        $ustadz = $this->createUstadz();
        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Kajian Foto Wajib',
            'tingkatan' => 'semua',
        ]);
        $ustadz->kegiatanPondok()->attach($kegiatan->id);
        $payload = $this->checkInPayload($kegiatan->id);
        unset($payload['foto_bukti']);

        $response = $this->actingAs($ustadz)->post(route('ustadz.presensi-kegiatan.store'), $payload);

        $response->assertSessionHasErrors('foto_bukti');
        $this->assertDatabaseCount('presensi_kegiatan', 0);
    }

    public function test_checkin_rejects_non_image_evidence_photo(): void
    {
        $ustadz = $this->createUstadz();
        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Kajian Foto Invalid',
            'tingkatan' => 'semua',
        ]);
        $ustadz->kegiatanPondok()->attach($kegiatan->id);

        $response = $this->actingAs($ustadz)->post(route('ustadz.presensi-kegiatan.store'), $this->checkInPayload($kegiatan->id, [
            'foto_bukti' => UploadedFile::fake()->create('bukti.jpg', 10, 'text/plain'),
        ]));

        $response->assertSessionHasErrors('foto_bukti');
        $this->assertDatabaseCount('presensi_kegiatan', 0);
    }

    public function test_photo_preview_is_available_to_owner_and_admin_only(): void
    {
        $ustadz = $this->createUstadz();
        $admin = $this->createAdmin();
        $otherUstadz = $this->createUstadz();
        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Kajian Foto Audit',
            'tingkatan' => 'semua',
        ]);
        $path = UploadedFile::fake()->image('bukti.jpg')->store('presensi-kegiatan', 'local');
        $session = PresensiKegiatan::create([
            'kegiatan_pondok_id' => $kegiatan->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => now()->toDateString(),
            'hari' => 'Rabu',
            'jam_mulai' => now()->format('H:i:s'),
            'foto_bukti' => $path,
            'status' => 'valid',
        ]);

        $photoUrl = route('presensi-kegiatan.photo', $session->id);
        $this->actingAs($ustadz)->get(route('ustadz.presensi-kegiatan.index'))
            ->assertOk()
            ->assertSee($photoUrl, false);
        $this->actingAs($admin)->get(route('admin.laporan.presensi-ustadz'))
            ->assertOk()
            ->assertSee($photoUrl, false);
        $this->actingAs($ustadz)->get($photoUrl)->assertOk();
        $this->actingAs($admin)->get($photoUrl)->assertOk();
        $this->actingAs($otherUstadz)->get($photoUrl)->assertForbidden();
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
            'tanggal' => now()->toDateString(),
            'hari' => 'Kamis',
            'jam_mulai' => '16:00:00',
            'latitude' => self::PESANTREN_LAT,
            'longitude' => self::PESANTREN_LON,
            'status' => 'valid',
        ]);

        $secondPayload = $this->checkInPayload($kegiatan->id);

        $response = $this->actingAs($ustadz)->post(route('ustadz.presensi-kegiatan.store'), $secondPayload);

        $this->assertTrue(
            $response->isClientError() || $response->isRedirection(),
            'Expected second check-in on same activity and date to be rejected'
        );

        $this->assertCount(1, PresensiKegiatan::where([
            'kegiatan_pondok_id' => $kegiatan->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => now()->toDateString(),
        ])->get());
    }

    public function test_ustadz_cannot_checkin_to_unassigned_activity(): void
    {
        $ustadz = $this->createUstadz();
        $unassignedKegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Kajian Kitab Kuning Bukan Tugasnya',
            'tingkatan' => 'aliyah',
        ]);

        $payload = $this->checkInPayload($unassignedKegiatan->id);

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
        ])->assertForbidden();
    }

    private function checkInPayload(int $kegiatanId, array $overrides = []): array
    {
        return array_merge([
            'kegiatan_pondok_id' => $kegiatanId,
            'latitude' => self::PESANTREN_LAT,
            'longitude' => self::PESANTREN_LON,
            'foto_bukti' => UploadedFile::fake()->image('bukti.jpg'),
        ], $overrides);
    }
}
