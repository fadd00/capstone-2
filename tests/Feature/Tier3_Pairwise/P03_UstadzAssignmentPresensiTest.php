<?php

namespace Tests\Feature\Tier3_Pairwise;

use App\Models\KegiatanPondok;
use App\Models\PresensiKegiatan;
use App\Models\PresensiSantri;
use App\Models\Santri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Tier 3 Pairwise Test: P03 Ustadz Assignment and Attendance Pipeline
 *
 * Source: dokum-cp1 §5.2 Q-ADM-09, §5.3 Q-UST-02..04, §5.2 Q-ADM-11..12, Build Plan §8
 * Verifies end-to-end integration across:
 * 1. Admin assigns Ustadz to Kegiatan Pondok (ustadz_kegiatan pivot)
 * 2. Ustadz submits GPS presensi with coordinates within 150m radius (status: valid)
 * 3. Ustadz records bulk attendance for santri participants
 * 4. Admin accesses global attendance reports reflecting the session
 * 5. Bulk presensi updates existing records idempotently (updateOrCreate)
 */
class P03_UstadzAssignmentPresensiTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    /**
     * 1. End-to-end assignment -> GPS check-in -> bulk santri attendance pipeline.
     */
    public function test_assignment_to_presensi_to_bulk_santri_attendance_pipeline(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz(['name' => 'Ustadz Abdullah']);

        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Tahsin & Tajwid Sore',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);

        // Step 1: Admin assigns Ustadz to Kegiatan
        $responseAssign = $this->actingAs($admin)->put(route('admin.penugasan-kegiatan.update', $ustadz->id), [
            'kegiatan_ids' => [$kegiatan->id],
        ]);
        $responseAssign->assertRedirect();
        $this->assertTrue($ustadz->kegiatanPondok()->where('kegiatan_pondok.id', $kegiatan->id)->exists());

        // Step 2: Ustadz checks in via GPS Haversine at campus center
        $presensiData = [
            'kegiatan_pondok_id' => $kegiatan->id,
            'tanggal' => now()->toDateString(),
            'hari' => 'Selasa',
            'jam_mulai' => '16:00',
            'latitude' => -7.797068,
            'longitude' => 110.370529,
            'materi' => 'Hukum Nun Mati dan Tanwin',
        ];

        $responsePresensi = $this->actingAs($ustadz)->post(route('ustadz.presensi-kegiatan.store'), $presensiData);
        $responsePresensi->assertRedirect();

        $presensiSession = PresensiKegiatan::where('kegiatan_pondok_id', $kegiatan->id)
            ->where('ustadz_id', $ustadz->id)
            ->firstOrFail();

        $this->assertEquals('valid', $presensiSession->status);

        // Step 3: Ustadz conducts bulk presensi for students in class
        $santri1 = Santri::create(['nama' => 'Santri Satu', 'nis' => '2026201', 'tingkatan' => 'tsanawiyah', 'kelas' => '7']);
        $santri2 = Santri::create(['nama' => 'Santri Dua', 'nis' => '2026202', 'tingkatan' => 'tsanawiyah', 'kelas' => '7']);

        $bulkPayload = [
            'kehadiran' => [
                $santri1->id => 'hadir',
                $santri2->id => 'izin',
            ],
        ];

        $responseBulk = $this->actingAs($ustadz)->post(
            route('ustadz.presensi-santri.store', $presensiSession->id),
            $bulkPayload
        );
        $responseBulk->assertRedirect();

        $this->assertDatabaseHas('presensi_santri', [
            'presensi_kegiatan_id' => $presensiSession->id,
            'santri_id' => $santri1->id,
            'status' => 'hadir',
        ]);
        $this->assertDatabaseHas('presensi_santri', [
            'presensi_kegiatan_id' => $presensiSession->id,
            'santri_id' => $santri2->id,
            'status' => 'izin',
        ]);
    }

    /**
     * 2. Admin global attendance reports reflect the ustadz and santri attendance records.
     */
    public function test_admin_presensi_reports_reflect_ustadz_and_santri_attendance_records(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz(['name' => 'Ustadz Luqman']);

        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Kajian Fiqih Ibadah',
            'tingkatan' => 'aliyah',
        ]);

        $presensiSession = PresensiKegiatan::create([
            'kegiatan_pondok_id' => $kegiatan->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => now()->toDateString(),
            'hari' => 'Kamis',
            'jam_mulai' => '08:00:00',
            'status' => 'valid',
            'materi' => 'Thaharah',
        ]);

        $santri = Santri::create([
            'nama' => 'Santri Fiqih',
            'nis' => '2026203',
            'tingkatan' => 'aliyah',
        ]);

        PresensiSantri::create([
            'presensi_kegiatan_id' => $presensiSession->id,
            'santri_id' => $santri->id,
            'status' => 'hadir',
        ]);

        // Admin checks Ustadz Attendance Report
        $responseUstadzReport = $this->actingAs($admin)->get(route('admin.laporan.presensi-ustadz'));
        $responseUstadzReport->assertStatus(200);
        $responseUstadzReport->assertSee('Ustadz Luqman');
        $responseUstadzReport->assertSee('Kajian Fiqih Ibadah');

        // Admin checks Santri Attendance Report
        $responseSantriReport = $this->actingAs($admin)->get(route('admin.laporan.presensi-santri'));
        $responseSantriReport->assertStatus(200);
        $responseSantriReport->assertSee('Santri Fiqih');
        $responseSantriReport->assertSee('hadir');
    }

    /**
     * 3. Ustadz bulk presensi updates existing records idempotently without duplicating rows.
     */
    public function test_ustadz_bulk_presensi_updates_existing_records_idempotently(): void
    {
        $ustadz = $this->createUstadz();
        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Muhadhoroh',
            'tingkatan' => 'tsanawiyah',
        ]);
        $ustadz->kegiatanPondok()->attach($kegiatan->id);

        $presensiSession = PresensiKegiatan::create([
            'kegiatan_pondok_id' => $kegiatan->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => now()->toDateString(),
            'hari' => 'Jumat',
            'jam_mulai' => '13:30:00',
            'status' => 'valid',
        ]);

        $santri = Santri::create([
            'nama' => 'Santri Idempotent',
            'nis' => '2026204',
            'tingkatan' => 'tsanawiyah',
        ]);

        // First submission: 'alpha'
        $this->actingAs($ustadz)->post(route('ustadz.presensi-santri.store', $presensiSession->id), [
            'kehadiran' => [$santri->id => 'alpha'],
        ])->assertRedirect();

        $this->assertEquals(1, PresensiSantri::where('presensi_kegiatan_id', $presensiSession->id)->count());
        $this->assertDatabaseHas('presensi_santri', [
            'presensi_kegiatan_id' => $presensiSession->id,
            'santri_id' => $santri->id,
            'status' => 'alpha',
        ]);

        // Second submission updating status to 'hadir'
        $this->actingAs($ustadz)->post(route('ustadz.presensi-santri.store', $presensiSession->id), [
            'kehadiran' => [$santri->id => 'hadir'],
        ])->assertRedirect();

        // Total count remains 1, status is updated
        $this->assertEquals(1, PresensiSantri::where('presensi_kegiatan_id', $presensiSession->id)->count());
        $this->assertDatabaseHas('presensi_santri', [
            'presensi_kegiatan_id' => $presensiSession->id,
            'santri_id' => $santri->id,
            'status' => 'hadir',
        ]);
    }
}
