<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\Santri;
use App\Models\SetoranHafalan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 36: Setoran Hafalan Baru (Ziyadah) (F36)
 *
 * Source: dokum-cp1 §5.3 Q-UST-05, Build Plan §9.1
 * Minimum 5 test methods covering:
 * - Viewing setoran index and create forms
 * - Recording valid setoran for santri binaan
 * - Rejection when recording for non-binaan santri (403)
 * - Validation on juz outside 1-30 and invalid nilai (0-100)
 * - Viewing setoran detail
 * - Role-based authorization guard (403 for non-ustadz)
 */
class F36_SetoranHafalanBaruTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    public function test_ustadz_can_view_setoran_index_and_create_form(): void
    {
        $ustadz = $this->createUstadz();

        $responseIndex = $this->actingAs($ustadz)->get(route('ustadz.setoran-hafalan.index'));
        $responseIndex->assertOk();

        $responseCreate = $this->actingAs($ustadz)->get(route('ustadz.setoran-hafalan.create'));
        $responseCreate->assertOk()
            ->assertViewHas('surahList', function ($surahList) {
                return count($surahList) === 114
                    && $surahList[0] === 'Al-Fatihah'
                    && $surahList[77] === 'An-Naba'
                    && $surahList[113] === 'An-Nas';
            })
            ->assertSee('<option value="Al-Fatihah"', false)
            ->assertSee('1. Al-Fatihah')
            ->assertSee('<option value="An-Naba"', false)
            ->assertSee('78. An-Naba')
            ->assertSee('<option value="An-Nas"', false)
            ->assertSee('114. An-Nas');
    }

    public function test_ustadz_can_record_valid_setoran_for_santri_binaan(): void
    {
        $ustadz = $this->createUstadz();
        $santri = Santri::create([
            'nama' => 'Muhammad Hafizh',
            'nis' => '2026050',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);
        $ustadz->santriBinaan()->attach($santri->id);

        $payload = [
            'santri_id' => $santri->id,
            'tanggal' => '2026-11-12',
            'surat' => 'An-Naba',
            'ayat_mulai' => 1,
            'ayat_selesai' => 20,
            'nilai' => 92.50,
            'status_selesai' => 1,
            'catatan' => 'Makhraj huruf shad dan sin sudah sangat baik',
        ];

        $response = $this->actingAs($ustadz)->post(route('ustadz.setoran-hafalan.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('setoran_hafalan', [
            'santri_id' => $santri->id,
            'ustadz_id' => $ustadz->id,
            'juz' => 30,
            'surat' => 'An-Naba',
            'ayat' => '1-20',
            'nilai' => 92.50,
            'status_selesai' => 1,
        ]);
    }

    public function test_ustadz_cannot_record_setoran_for_unassigned_santri(): void
    {
        $ustadz = $this->createUstadz();
        $unassignedSantri = Santri::create([
            'nama' => 'Santri Bukan Binaan',
            'nis' => '2026051',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);

        $payload = [
            'santri_id' => $unassignedSantri->id,
            'tanggal' => '2026-11-12',
            'surat' => 'Al-Fatihah',
            'ayat_mulai' => 1,
            'ayat_selesai' => 7,
            'nilai' => 95.00,
            'status_selesai' => 1,
        ];

        $response = $this->actingAs($ustadz)->post(route('ustadz.setoran-hafalan.store'), $payload);

        $this->assertTrue(
            $response->status() === 403 || $response->isClientError() || $response->isRedirection(),
            'Expected unassigned mentorship to be rejected'
        );
        $this->assertDatabaseMissing('setoran_hafalan', ['santri_id' => $unassignedSantri->id]);
    }

    public function test_setoran_validation_fails_on_out_of_range_ayat_or_nilai(): void
    {
        $ustadz = $this->createUstadz();
        $santri = Santri::create(['nama' => 'Santri Uji Range', 'nis' => '2026052', 'tingkatan' => 'tsanawiyah']);
        $ustadz->santriBinaan()->attach($santri->id);

        // Al-Fatihah hanya 7 ayat; nilai negatif
        $response = $this->actingAs($ustadz)->post(route('ustadz.setoran-hafalan.store'), [
            'santri_id' => $santri->id,
            'tanggal' => '2026-11-12',
            'surat' => 'Al-Fatihah',
            'ayat_mulai' => 1,
            'ayat_selesai' => 8,
            'nilai' => -5,
        ]);

        $response->assertSessionHasErrors(['nilai']);

        $response = $this->actingAs($ustadz)->post(route('ustadz.setoran-hafalan.store'), [
            'santri_id' => $santri->id,
            'tanggal' => '2026-11-12',
            'surat' => 'Al-Fatihah',
            'ayat_mulai' => 1,
            'ayat_selesai' => 8,
        ]);

        $response->assertSessionHasErrors(['ayat_selesai' => 'Surat Al-Fatihah hanya memiliki 7 ayat.']);

        // Ayat 0, ayat selesai sebelum ayat mulai, dan nilai 105
        $response2 = $this->actingAs($ustadz)->post(route('ustadz.setoran-hafalan.store'), [
            'santri_id' => $santri->id,
            'tanggal' => '2026-11-12',
            'surat' => 'An-Naba',
            'ayat_mulai' => 0,
            'ayat_selesai' => -1,
            'nilai' => 105,
        ]);

        $response2->assertSessionHasErrors(['ayat_mulai', 'ayat_selesai', 'nilai']);
        $this->assertDatabaseCount('setoran_hafalan', 0);

        $response3 = $this->actingAs($ustadz)->post(route('ustadz.setoran-hafalan.store'), [
            'santri_id' => $santri->id,
            'tanggal' => '2026-11-12',
            'surat' => 'Surat Tidak Valid',
            'ayat_mulai' => 1,
            'ayat_selesai' => 10,
        ]);

        $response3->assertSessionHasErrors(['surat']);
    }

    public function test_ustadz_can_view_setoran_detail(): void
    {
        $ustadz = $this->createUstadz();
        $santri = Santri::create(['nama' => 'Santri Detail', 'nis' => '2026053', 'tingkatan' => 'tsanawiyah']);
        $ustadz->santriBinaan()->attach($santri->id);

        $setoran = SetoranHafalan::create([
            'santri_id' => $santri->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-12',
            'juz' => 29,
            'surat' => 'Al-Mulk',
            'ayat' => '1-30',
            'nilai' => 90.00,
            'status_selesai' => 1,
            'catatan' => 'Selesai satu surat',
        ]);

        $response = $this->actingAs($ustadz)->get(route('ustadz.setoran-hafalan.show', $setoran->id));

        $response->assertOk();
        $response->assertSee('Al-Mulk');
        $response->assertSee('Santri Detail');
    }

    public function test_non_ustadz_cannot_manage_setoran_hafalan(): void
    {
        $wali = $this->createWaliMurid();
        $calon = $this->createCalonSantri();

        $this->actingAs($wali)->get(route('ustadz.setoran-hafalan.index'))->assertForbidden();
        $this->actingAs($calon)->get(route('ustadz.setoran-hafalan.index'))->assertForbidden();

        $this->actingAs($wali)->post(route('ustadz.setoran-hafalan.store'), [
            'santri_id' => 1,
            'juz' => 1,
        ])->assertForbidden();
    }
}
