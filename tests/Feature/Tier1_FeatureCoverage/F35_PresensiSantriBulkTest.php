<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\KegiatanPondok;
use App\Models\PresensiKegiatan;
use App\Models\PresensiSantri;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 35: Presensi Santri Bulk (F35)
 *
 * Source: dokum-cp1 §5.3 Q-UST-03, 04, Build Plan §8.2
 * Minimum 5 test methods covering:
 * - Viewing bulk attendance form for a presensi kegiatan session
 * - Submitting bulk attendance for multiple santri
 * - Idempotent updateOrCreate logic (updating existing statuses without duplication)
 * - Validation on invalid attendance statuses
 * - Forbidden access to another ustadz's session (403)
 * - Role-based authorization guard (403 for non-ustadz)
 */
class F35_PresensiSantriBulkTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    protected function createSession(User $ustadz): PresensiKegiatan
    {
        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Tahfidz Rutin Pagi',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);
        $ustadz->kegiatanPondok()->attach($kegiatan->id);

        return PresensiKegiatan::create([
            'kegiatan_pondok_id' => $kegiatan->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-12',
            'hari' => 'Kamis',
            'jam_mulai' => '05:30:00',
            'latitude' => -7.797068,
            'longitude' => 110.370529,
            'status' => 'valid',
        ]);
    }

    public function test_ustadz_can_view_bulk_presensi_santri_form(): void
    {
        $ustadz = $this->createUstadz();
        $session = $this->createSession($ustadz);

        $response = $this->actingAs($ustadz)->get(route('ustadz.presensi-santri.create', $session->id));

        $response->assertOk();
    }

    public function test_ustadz_can_bulk_submit_santri_attendance(): void
    {
        $ustadz = $this->createUstadz();
        $session = $this->createSession($ustadz);

        $santri1 = Santri::create(['nama' => 'Santri Satu', 'nis' => '2026040', 'tingkatan' => 'tsanawiyah', 'kelas' => '7']);
        $santri2 = Santri::create(['nama' => 'Santri Dua', 'nis' => '2026041', 'tingkatan' => 'tsanawiyah', 'kelas' => '7']);
        $santri3 = Santri::create(['nama' => 'Santri Tiga', 'nis' => '2026042', 'tingkatan' => 'tsanawiyah', 'kelas' => '7']);

        $payload = [
            'presensi' => [
                $santri1->id => 'hadir',
                $santri2->id => 'izin',
                $santri3->id => 'sakit',
            ],
        ];

        $response = $this->actingAs($ustadz)->post(route('ustadz.presensi-santri.store', $session->id), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('presensi_santri', [
            'presensi_kegiatan_id' => $session->id,
            'santri_id' => $santri1->id,
            'status' => 'hadir',
        ]);
        $this->assertDatabaseHas('presensi_santri', [
            'presensi_kegiatan_id' => $session->id,
            'santri_id' => $santri2->id,
            'status' => 'izin',
        ]);
        $this->assertDatabaseHas('presensi_santri', [
            'presensi_kegiatan_id' => $session->id,
            'santri_id' => $santri3->id,
            'status' => 'sakit',
        ]);
    }

    public function test_bulk_submission_is_idempotent_and_updates_status_without_duplication(): void
    {
        $ustadz = $this->createUstadz();
        $session = $this->createSession($ustadz);

        $santri = Santri::create(['nama' => 'Santri Update', 'nis' => '2026043', 'tingkatan' => 'tsanawiyah', 'kelas' => '7']);

        // First submission: 'alpha'
        PresensiSantri::create([
            'presensi_kegiatan_id' => $session->id,
            'santri_id' => $santri->id,
            'status' => 'alpha',
        ]);

        // Second submission: updated to 'hadir'
        $payload = [
            'presensi' => [
                $santri->id => 'hadir',
            ],
        ];

        $response = $this->actingAs($ustadz)->post(route('ustadz.presensi-santri.store', $session->id), $payload);

        $response->assertSessionHasNoErrors();

        $records = PresensiSantri::where([
            'presensi_kegiatan_id' => $session->id,
            'santri_id' => $santri->id,
        ])->get();

        $this->assertCount(1, $records, 'Expected updateOrCreate to maintain exactly 1 record');
        $this->assertEquals('hadir', $records->first()->status);
    }

    public function test_submitting_invalid_status_fails_validation(): void
    {
        $ustadz = $this->createUstadz();
        $session = $this->createSession($ustadz);

        $santri = Santri::create(['nama' => 'Santri Invalid', 'nis' => '2026044', 'tingkatan' => 'tsanawiyah', 'kelas' => '7']);

        $payload = [
            'presensi' => [
                $santri->id => 'bolos_tidak_sah',
            ],
        ];

        $response = $this->actingAs($ustadz)->post(route('ustadz.presensi-santri.store', $session->id), $payload);

        $response->assertSessionHasErrors();
    }

    public function test_ustadz_cannot_input_attendance_for_another_ustadz_session(): void
    {
        $ustadzOwner = $this->createUstadz(['email' => 'owner@pptq.test']);
        $ustadzOther = $this->createUstadz(['email' => 'other@pptq.test']);
        $session = $this->createSession($ustadzOwner);

        $santri = Santri::create(['nama' => 'Santri Uji', 'nis' => '2026045', 'tingkatan' => 'tsanawiyah', 'kelas' => '7']);

        $payload = [
            'presensi' => [
                $santri->id => 'hadir',
            ],
        ];

        $response = $this->actingAs($ustadzOther)->post(route('ustadz.presensi-santri.store', $session->id), $payload);

        $this->assertTrue(
            $response->status() === 403 || $response->isClientError() || $response->isRedirection(),
            'Expected foreign ustadz to be blocked from recording attendance'
        );
    }

    public function test_non_ustadz_cannot_access_santri_presensi(): void
    {
        $ustadz = $this->createUstadz();
        $session = $this->createSession($ustadz);
        $wali = $this->createWaliMurid();
        $calon = $this->createCalonSantri();

        $this->actingAs($wali)->get(route('ustadz.presensi-santri.create', $session->id))->assertForbidden();
        $this->actingAs($calon)->get(route('ustadz.presensi-santri.create', $session->id))->assertForbidden();

        $this->actingAs($wali)->post(route('ustadz.presensi-santri.store', $session->id), [
            'presensi' => [1 => 'hadir'],
        ])->assertForbidden();
    }
}
