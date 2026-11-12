<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\Murojaah;
use App\Models\Santri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 37: Murojaah Hafalan (F37)
 *
 * Source: dokum-cp1 §5.3 Q-UST-05, Build Plan §9.2
 * Minimum 5 test methods covering:
 * - Viewing murojaah index and create forms
 * - Recording valid murojaah session for santri binaan
 * - Rejection when recording murojaah for unassigned santri (403)
 * - Validation on invalid juz range and missing fields
 * - Viewing murojaah detail
 * - Role-based authorization guard (403 for non-ustadz)
 */
class F37_MurojaahHafalanTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    public function test_ustadz_can_view_murojaah_index_and_create_form(): void
    {
        $ustadz = $this->createUstadz();

        $responseIndex = $this->actingAs($ustadz)->get(route('ustadz.murojaah.index'));
        $responseIndex->assertOk();

        $responseCreate = $this->actingAs($ustadz)->get(route('ustadz.murojaah.create'));
        $responseCreate->assertOk();
    }

    public function test_ustadz_can_record_valid_murojaah_for_santri_binaan(): void
    {
        $ustadz = $this->createUstadz();
        $santri = Santri::create([
            'nama' => 'Muhammad Hafizh',
            'nis' => '2026060',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);
        $ustadz->santriBinaan()->attach($santri->id);

        $payload = [
            'santri_id' => $santri->id,
            'tanggal' => '2026-11-12',
            'juz' => 30,
            'surat' => 'An-Nazi\'at',
            'ayat' => '1-46',
            'nilai' => 95.00,
            'status_selesai' => 1,
        ];

        $response = $this->actingAs($ustadz)->post(route('ustadz.murojaah.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('murojaah', [
            'santri_id' => $santri->id,
            'ustadz_id' => $ustadz->id,
            'juz' => 30,
            'surat' => 'An-Nazi\'at',
            'ayat' => '1-46',
            'nilai' => 95.00,
            'status_selesai' => 1,
        ]);
    }

    public function test_ustadz_cannot_record_murojaah_for_unassigned_santri(): void
    {
        $ustadz = $this->createUstadz();
        $unassignedSantri = Santri::create([
            'nama' => 'Santri Lain',
            'nis' => '2026061',
            'tingkatan' => 'tsanawiyah',
        ]);

        $payload = [
            'santri_id' => $unassignedSantri->id,
            'tanggal' => '2026-11-12',
            'juz' => 30,
            'surat' => 'Abasa',
            'ayat' => '1-42',
            'nilai' => 90.00,
        ];

        $response = $this->actingAs($ustadz)->post(route('ustadz.murojaah.store'), $payload);

        $this->assertTrue(
            $response->status() === 403 || $response->isClientError() || $response->isRedirection(),
            'Expected unassigned mentorship check to reject'
        );
        $this->assertDatabaseMissing('murojaah', ['santri_id' => $unassignedSantri->id]);
    }

    public function test_murojaah_validation_fails_on_invalid_juz_or_missing_surat(): void
    {
        $ustadz = $this->createUstadz();
        $santri = Santri::create(['nama' => 'Santri Murojaah Test', 'nis' => '2026062', 'tingkatan' => 'tsanawiyah']);
        $ustadz->santriBinaan()->attach($santri->id);

        $response = $this->actingAs($ustadz)->post(route('ustadz.murojaah.store'), [
            'santri_id' => $santri->id,
            'tanggal' => '2026-11-12',
            'juz' => 35,
            'surat' => '',
            'ayat' => '1-10',
        ]);

        $response->assertSessionHasErrors(['juz', 'surat']);
    }

    public function test_ustadz_can_view_murojaah_detail(): void
    {
        $ustadz = $this->createUstadz();
        $santri = Santri::create(['nama' => 'Santri Detail Murojaah', 'nis' => '2026063', 'tingkatan' => 'tsanawiyah']);
        $ustadz->santriBinaan()->attach($santri->id);

        $murojaah = Murojaah::create([
            'santri_id' => $santri->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-12',
            'juz' => 29,
            'surat' => 'Al-Qalam',
            'ayat' => '1-52',
            'nilai' => 94.00,
            'status_selesai' => 1,
        ]);

        $response = $this->actingAs($ustadz)->get(route('ustadz.murojaah.show', $murojaah->id));

        $response->assertOk();
        $response->assertSee('Al-Qalam');
        $response->assertSee('Santri Detail Murojaah');
    }

    public function test_non_ustadz_cannot_access_murojaah(): void
    {
        $wali = $this->createWaliMurid();
        $calon = $this->createCalonSantri();

        $this->actingAs($wali)->get(route('ustadz.murojaah.index'))->assertForbidden();
        $this->actingAs($calon)->get(route('ustadz.murojaah.index'))->assertForbidden();

        $this->actingAs($wali)->post(route('ustadz.murojaah.store'), [
            'santri_id' => 1,
            'juz' => 1,
        ])->assertForbidden();
    }
}
