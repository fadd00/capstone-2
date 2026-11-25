<?php

namespace Tests\Feature\Tier4_Scenarios;

use App\Models\KegiatanPondok;
use App\Models\Murojaah;
use App\Models\PresensiKegiatan;
use App\Models\Santri;
use App\Models\SetoranHafalan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Tier 4 Scenario Test: S03 Ustadz Daily Teaching Journey
 *
 * Source: dokum-cp1 §5.3, §6, Build Plan §8, §9, §16.2
 * Verifies realistic daily routines of teaching staff (ustadz):
 * - Routine 1: Morning Teaching (Dashboard schedule review -> GPS check-in at campus coordinates ->
 *              Status marked 'valid' -> Bulk attendance recording for santri in class -> View session details).
 * - Routine 2: Afternoon Tahfidz Halaqa (View santri binaan -> Record Setoran Ziyadah ->
 *              Record Murojaah -> Review history logs and detail views).
 */
class S03_UstadzDailyTeachingJourneyTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    /**
     * 1. Ustadz Daily Morning Teaching and Attendance Routine.
     */
    public function test_ustadz_daily_morning_teaching_and_attendance_routine(): void
    {
        $ustadz = $this->createUstadz(['name' => 'Ustadz Ridwan']);

        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Kajian Kitab Bulughul Maram',
            'tingkatan' => 'aliyah',
            'kelas' => '11',
            'deskripsi' => 'Kajian hadits hukum tematik setiap pagi.',
        ]);

        $ustadz->kegiatanPondok()->attach($kegiatan->id);

        $santriA = Santri::create(['nama' => 'Santri Aliyah 1', 'nis' => '2026601', 'tingkatan' => 'aliyah', 'kelas' => '11']);
        $santriB = Santri::create(['nama' => 'Santri Aliyah 2', 'nis' => '2026602', 'tingkatan' => 'aliyah', 'kelas' => '11']);

        // Step 1: Ustadz logs in and visits dashboard
        $dashboardResponse = $this->actingAs($ustadz)->get(route('ustadz.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Kajian Kitab Bulughul Maram');

        // Step 2: Ustadz opens GPS check-in page
        $createPresensiResponse = $this->actingAs($ustadz)->get(route('ustadz.presensi-kegiatan.create'));
        $createPresensiResponse->assertStatus(200);

        // Step 3: Ustadz submits GPS presensi at pesantren coordinates (-7.797068, 110.370529)
        $presensiData = [
            'kegiatan_pondok_id' => $kegiatan->id,
            'jam_selesai' => '08:30',
            'materi' => 'Bab Sholat Berjamaah',
            'latitude' => -7.797068,
            'longitude' => 110.370529,
            'foto_bukti' => UploadedFile::fake()->image('bukti-pagi.jpg'),
        ];

        $submitPresensiResponse = $this->actingAs($ustadz)->post(
            route('ustadz.presensi-kegiatan.store'),
            $presensiData
        );
        $submitPresensiResponse->assertRedirect();

        $session = PresensiKegiatan::where('kegiatan_pondok_id', $kegiatan->id)
            ->where('ustadz_id', $ustadz->id)
            ->firstOrFail();

        $this->assertEquals('valid', $session->status);

        // Step 4: Ustadz opens bulk presensi form for students
        $bulkFormResponse = $this->actingAs($ustadz)->get(
            route('ustadz.presensi-santri.create', $session->id)
        );
        $bulkFormResponse->assertStatus(200);

        // Step 5: Ustadz marks attendance: Santri A hadir, Santri B sakit
        $bulkSubmitResponse = $this->actingAs($ustadz)->post(
            route('ustadz.presensi-santri.store', $session->id),
            [
                'kehadiran' => [
                    $santriA->id => 'hadir',
                    $santriB->id => 'sakit',
                ],
            ]
        );
        $bulkSubmitResponse->assertRedirect();

        $this->assertDatabaseHas('presensi_santri', [
            'presensi_kegiatan_id' => $session->id,
            'santri_id' => $santriA->id,
            'status' => 'hadir',
        ]);
        $this->assertDatabaseHas('presensi_santri', [
            'presensi_kegiatan_id' => $session->id,
            'santri_id' => $santriB->id,
            'status' => 'sakit',
        ]);

        // Step 6: Ustadz views session detail
        $detailResponse = $this->actingAs($ustadz)->get(
            route('ustadz.presensi-kegiatan.show', $session->id)
        );
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Bab Sholat Berjamaah');
    }

    /**
     * 2. Ustadz Daily Afternoon Tahfidz Halaqa Routine.
     */
    public function test_ustadz_daily_afternoon_tahfidz_halaqa_routine(): void
    {
        $ustadz = $this->createUstadz(['name' => 'Ustadz Muhaimin']);

        $santri1 = Santri::create(['nama' => 'Santri Tahfidz 1', 'nis' => '2026603', 'tingkatan' => 'tsanawiyah']);
        $santri2 = Santri::create(['nama' => 'Santri Tahfidz 2', 'nis' => '2026604', 'tingkatan' => 'tsanawiyah']);

        $ustadz->santriBinaan()->attach([$santri1->id, $santri2->id]);

        // Step 1: Ustadz records Setoran Ziyadah for Santri 1
        $createSetoranResponse = $this->actingAs($ustadz)->get(route('ustadz.setoran-hafalan.create'));
        $createSetoranResponse->assertStatus(200);

        $setoranPayload = [
            'santri_id' => $santri1->id,
            'tanggal' => now()->toDateString(),
            'surat' => 'An-Naba',
            'ayat_mulai' => 1,
            'ayat_selesai' => 40,
            'nilai' => 88.50,
            'status_selesai' => 1,
            'catatan' => 'Bagus dan lancar, mad thabi’i terjaga.',
        ];

        $this->actingAs($ustadz)->post(route('ustadz.setoran-hafalan.store'), $setoranPayload)
            ->assertRedirect();

        $setoran = SetoranHafalan::where('santri_id', $santri1->id)->firstOrFail();
        $this->assertEquals(30, $setoran->juz);

        // Step 2: Ustadz records Murojaah for Santri 2
        $createMurojaahResponse = $this->actingAs($ustadz)->get(route('ustadz.murojaah.create'));
        $createMurojaahResponse->assertStatus(200);

        $murojaahPayload = [
            'santri_id' => $santri2->id,
            'tanggal' => now()->toDateString(),
            'surat' => 'Al-Mulk',
            'ayat_mulai' => 1,
            'ayat_selesai' => 30,
            'nilai' => 91.00,
            'status_selesai' => 1,
            'catatan' => 'Lancar, terus istiqomah murajaah mandiri.',
        ];

        $this->actingAs($ustadz)->post(route('ustadz.murojaah.store'), $murojaahPayload)
            ->assertRedirect();

        $murojaah = Murojaah::where('santri_id', $santri2->id)->firstOrFail();
        $this->assertEquals(29, $murojaah->juz);

        // Step 3: Review history lists and individual show pages
        $setoranListResponse = $this->actingAs($ustadz)->get(route('ustadz.setoran-hafalan.index'));
        $setoranListResponse->assertStatus(200);
        $setoranListResponse->assertSee('Santri Tahfidz 1');

        $this->actingAs($ustadz)->get(route('ustadz.setoran-hafalan.show', $setoran->id))->assertStatus(200);

        $murojaahListResponse = $this->actingAs($ustadz)->get(route('ustadz.murojaah.index'));
        $murojaahListResponse->assertStatus(200);
        $murojaahListResponse->assertSee('Santri Tahfidz 2');

        $this->actingAs($ustadz)->get(route('ustadz.murojaah.show', $murojaah->id))->assertStatus(200);
    }

    /**
     * 3. Ustadz Out-of-Bounds Geofence Handling and Attendance Recording Journey.
     *
     * Verifies the end-to-end journey when an ustadz attempts GPS check-in outside
     * the 150m geofence radius (recorded as 'invalid'), repositions inside the campus
     * boundary to submit a 'valid' check-in, and successfully completes bulk attendance
     * for assigned students.
     */
    public function test_ustadz_out_of_bounds_geofence_handling_and_attendance_recording_journey(): void
    {
        $ustadz = $this->createUstadz(['name' => 'Ustadz Zakaria']);

        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Halaqah Hadits Sore',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '8B',
            'deskripsi' => 'Kajian hadits arbain tematik.',
        ]);

        $ustadz->kegiatanPondok()->attach($kegiatan->id);

        $santri1 = Santri::create(['nama' => 'Santri Tsanawiyah 1', 'nis' => '2026801', 'tingkatan' => 'tsanawiyah', 'kelas' => '8B']);
        $santri2 = Santri::create(['nama' => 'Santri Tsanawiyah 2', 'nis' => '2026802', 'tingkatan' => 'tsanawiyah', 'kelas' => '8B']);
        $santri3 = Santri::create(['nama' => 'Santri Tsanawiyah 3', 'nis' => '2026803', 'tingkatan' => 'tsanawiyah', 'kelas' => '8B']);

        // Step 1: Out-of-bounds check-in attempt (~500m south from pesantren)
        $invalidPresensiData = [
            'kegiatan_pondok_id' => $kegiatan->id,
            'jam_selesai' => '17:15',
            'materi' => 'Hadits ke-5: Perkara Baru Dalam Agama',
            'latitude' => -7.801500,
            'longitude' => 110.370529,
            'foto_bukti' => UploadedFile::fake()->image('bukti-luar-radius.jpg'),
        ];

        $outOfBoundsResponse = $this->actingAs($ustadz)->post(
            route('ustadz.presensi-kegiatan.store'),
            $invalidPresensiData
        );
        $outOfBoundsResponse->assertRedirect();

        $invalidSession = PresensiKegiatan::where('kegiatan_pondok_id', $kegiatan->id)
            ->where('ustadz_id', $ustadz->id)
            ->where('status', 'invalid')
            ->firstOrFail();
        $this->assertEquals('invalid', $invalidSession->status);

        // Step 2: Valid check-in within pesantren geofence perimeter (-7.797068, 110.370529)
        $this->travelTo(now()->addDay());
        $validPresensiData = [
            'kegiatan_pondok_id' => $kegiatan->id,
            'jam_selesai' => '17:15',
            'materi' => 'Hadits ke-6: Halal dan Haram',
            'latitude' => -7.797068,
            'longitude' => 110.370529,
            'foto_bukti' => UploadedFile::fake()->image('bukti-dalam-radius.jpg'),
        ];

        $validResponse = $this->actingAs($ustadz)->post(
            route('ustadz.presensi-kegiatan.store'),
            $validPresensiData
        );
        $validResponse->assertRedirect();
        $this->travelBack();

        $validSession = PresensiKegiatan::where('kegiatan_pondok_id', $kegiatan->id)
            ->where('ustadz_id', $ustadz->id)
            ->where('status', 'valid')
            ->firstOrFail();
        $this->assertEquals('valid', $validSession->status);

        // Step 3: Record bulk attendance for students in the valid session
        $attendanceData = [
            'kehadiran' => [
                $santri1->id => 'hadir',
                $santri2->id => 'izin',
                $santri3->id => 'sakit',
            ],
        ];

        $submitAttendance = $this->actingAs($ustadz)->post(
            route('ustadz.presensi-santri.store', $validSession->id),
            $attendanceData
        );
        $submitAttendance->assertRedirect();

        // Step 4: Verify attendance records in database
        $this->assertDatabaseHas('presensi_santri', [
            'presensi_kegiatan_id' => $validSession->id,
            'santri_id' => $santri1->id,
            'status' => 'hadir',
        ]);
        $this->assertDatabaseHas('presensi_santri', [
            'presensi_kegiatan_id' => $validSession->id,
            'santri_id' => $santri2->id,
            'status' => 'izin',
        ]);
        $this->assertDatabaseHas('presensi_santri', [
            'presensi_kegiatan_id' => $validSession->id,
            'santri_id' => $santri3->id,
            'status' => 'sakit',
        ]);

        // Step 5: View valid session details
        $detailResponse = $this->actingAs($ustadz)->get(
            route('ustadz.presensi-kegiatan.show', $validSession->id)
        );
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('valid');
        $detailResponse->assertSee('Hadits ke-6: Halal dan Haram');
    }
}
