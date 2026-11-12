<?php

namespace Tests\Feature\Tier2_Boundary;

use App\Models\Santri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Tier 2 Boundary Test: B05 Tahfidz Range Boundary
 *
 * Source: dokum-cp1 §5.3 Q-UST-05, Build Plan §9.1
 * Verifies boundaries for Setoran Ziyadah and Murojaah inputs:
 * - Juz 1 (minimum) and Juz 30 (maximum) are valid
 * - Juz 0 (underflow) and Juz 31 (overflow) are rejected
 * - Nilai 0.00 (minimum) and Nilai 100.00 (maximum) are valid
 * - Nilai 100.01 (overflow) and negative values are rejected
 * - Empty/whitespace surat and ayat strings are rejected
 * - Applies symmetrically to both Setoran and Murojaah endpoints
 */
class B05_TahfidzRangeBoundaryTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    protected function setupUstadzWithSantriBinaan(): array
    {
        $ustadz = $this->createUstadz();
        $santri = Santri::create([
            'nama' => 'Santri Tahfidz Test',
            'nis' => '2026101',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7A',
        ]);

        $ustadz->santriBinaan()->attach($santri->id);

        return [$ustadz, $santri];
    }

    /**
     * 1. Juz 1 (minimum) and Juz 30 (maximum) are accepted for Setoran Ziyadah.
     */
    public function test_minimum_juz_1_and_maximum_juz_30_are_accepted_for_setoran(): void
    {
        [$ustadz, $santri] = $this->setupUstadzWithSantriBinaan();

        // Juz 1
        $payloadJuz1 = [
            'santri_id' => $santri->id,
            'tanggal' => now()->toDateString(),
            'juz' => 1,
            'surat' => 'Al-Baqarah',
            'ayat' => '1-20',
            'nilai' => 85.00,
        ];
        $response1 = $this->actingAs($ustadz)->post(route('ustadz.setoran-hafalan.store'), $payloadJuz1);
        $response1->assertSessionHasNoErrors();
        $response1->assertRedirect();
        $this->assertDatabaseHas('setoran_hafalan', ['santri_id' => $santri->id, 'juz' => 1]);

        // Juz 30
        $payloadJuz30 = [
            'santri_id' => $santri->id,
            'tanggal' => now()->toDateString(),
            'juz' => 30,
            'surat' => 'An-Naba',
            'ayat' => '1-40',
            'nilai' => 90.00,
        ];
        $response30 = $this->actingAs($ustadz)->post(route('ustadz.setoran-hafalan.store'), $payloadJuz30);
        $response30->assertSessionHasNoErrors();
        $response30->assertRedirect();
        $this->assertDatabaseHas('setoran_hafalan', ['santri_id' => $santri->id, 'juz' => 30]);
    }

    /**
     * 2. Juz 0 (underflow) and Juz 31 (overflow) are rejected by validation.
     */
    public function test_juz_below_minimum_0_and_above_maximum_31_are_rejected(): void
    {
        [$ustadz, $santri] = $this->setupUstadzWithSantriBinaan();

        // Juz 0
        $payloadJuz0 = [
            'santri_id' => $santri->id,
            'tanggal' => now()->toDateString(),
            'juz' => 0,
            'surat' => 'Al-Fatihah',
            'ayat' => '1-7',
            'nilai' => 80.00,
        ];
        $response0 = $this->actingAs($ustadz)->post(route('ustadz.setoran-hafalan.store'), $payloadJuz0);
        $response0->assertSessionHasErrors(['juz']);

        // Juz 31
        $payloadJuz31 = [
            'santri_id' => $santri->id,
            'tanggal' => now()->toDateString(),
            'juz' => 31,
            'surat' => 'Surat Invalid',
            'ayat' => '1-10',
            'nilai' => 80.00,
        ];
        $response31 = $this->actingAs($ustadz)->post(route('ustadz.setoran-hafalan.store'), $payloadJuz31);
        $response31->assertSessionHasErrors(['juz']);
    }

    /**
     * 3. Nilai boundaries: exact 0.00 (minimum) and exact 100.00 (maximum) are accepted.
     */
    public function test_nilai_boundaries_exact_0_and_exact_100_are_accepted(): void
    {
        [$ustadz, $santri] = $this->setupUstadzWithSantriBinaan();

        // Nilai 0.00
        $payload0 = [
            'santri_id' => $santri->id,
            'tanggal' => now()->toDateString(),
            'juz' => 2,
            'surat' => 'Al-Baqarah',
            'ayat' => '142-150',
            'nilai' => 0.00,
        ];
        $response0 = $this->actingAs($ustadz)->post(route('ustadz.setoran-hafalan.store'), $payload0);
        $response0->assertSessionHasNoErrors();
        $this->assertDatabaseHas('setoran_hafalan', ['santri_id' => $santri->id, 'juz' => 2, 'nilai' => 0.00]);

        // Nilai 100.00
        $payload100 = [
            'santri_id' => $santri->id,
            'tanggal' => now()->toDateString(),
            'juz' => 3,
            'surat' => 'Ali Imran',
            'ayat' => '1-20',
            'nilai' => 100.00,
        ];
        $response100 = $this->actingAs($ustadz)->post(route('ustadz.setoran-hafalan.store'), $payload100);
        $response100->assertSessionHasNoErrors();
        $this->assertDatabaseHas('setoran_hafalan', ['santri_id' => $santri->id, 'juz' => 3, 'nilai' => 100.00]);
    }

    /**
     * 4. Nilai > 100.00 (e.g. 100.01) or negative values are rejected by validation.
     */
    public function test_nilai_exceeding_100_or_negative_is_rejected(): void
    {
        [$ustadz, $santri] = $this->setupUstadzWithSantriBinaan();

        // Nilai 100.01
        $payload1001 = [
            'santri_id' => $santri->id,
            'tanggal' => now()->toDateString(),
            'juz' => 4,
            'surat' => 'An-Nisa',
            'ayat' => '1-10',
            'nilai' => 100.01,
        ];
        $response1001 = $this->actingAs($ustadz)->post(route('ustadz.setoran-hafalan.store'), $payload1001);
        $response1001->assertSessionHasErrors(['nilai']);

        // Nilai -1.00
        $payloadNeg = [
            'santri_id' => $santri->id,
            'tanggal' => now()->toDateString(),
            'juz' => 4,
            'surat' => 'An-Nisa',
            'ayat' => '1-10',
            'nilai' => -1.00,
        ];
        $responseNeg = $this->actingAs($ustadz)->post(route('ustadz.setoran-hafalan.store'), $payloadNeg);
        $responseNeg->assertSessionHasErrors(['nilai']);
    }

    /**
     * 5. Empty or whitespace-only surat and ayat are rejected.
     */
    public function test_empty_or_whitespace_surat_and_ayat_are_rejected(): void
    {
        [$ustadz, $santri] = $this->setupUstadzWithSantriBinaan();

        $invalidPayloads = [
            'empty surat' => ['surat' => '', 'ayat' => '1-10'],
            'whitespace surat' => ['surat' => '   ', 'ayat' => '1-10'],
            'empty ayat' => ['surat' => 'Al-Baqarah', 'ayat' => ''],
            'whitespace ayat' => ['surat' => 'Al-Baqarah', 'ayat' => '   '],
        ];

        foreach ($invalidPayloads as $desc => $fields) {
            $payload = array_merge([
                'santri_id' => $santri->id,
                'tanggal' => now()->toDateString(),
                'juz' => 1,
                'nilai' => 85.00,
            ], $fields);

            $response = $this->actingAs($ustadz)->post(route('ustadz.setoran-hafalan.store'), $payload);
            $response->assertSessionHasErrors(array_keys($fields));
        }
    }

    /**
     * 6. Murojaah endpoints enforce the identical juz and nilai boundary limits.
     */
    public function test_murojaah_also_enforces_identical_juz_and_nilai_boundaries(): void
    {
        [$ustadz, $santri] = $this->setupUstadzWithSantriBinaan();

        // Valid Murojaah at boundary Juz 30, Nilai 100
        $validMurojaah = [
            'santri_id' => $santri->id,
            'tanggal' => now()->toDateString(),
            'juz' => 30,
            'surat' => 'An-Nas',
            'ayat' => '1-6',
            'nilai' => 100.00,
        ];
        $responseValid = $this->actingAs($ustadz)->post(route('ustadz.murojaah.store'), $validMurojaah);
        $responseValid->assertSessionHasNoErrors();
        $this->assertDatabaseHas('murojaah', ['santri_id' => $santri->id, 'juz' => 30, 'nilai' => 100.00]);

        // Invalid Murojaah: Juz 35 and Nilai 150
        $invalidMurojaah = [
            'santri_id' => $santri->id,
            'tanggal' => now()->toDateString(),
            'juz' => 35,
            'surat' => 'An-Nas',
            'ayat' => '1-6',
            'nilai' => 150.00,
        ];
        $responseInvalid = $this->actingAs($ustadz)->post(route('ustadz.murojaah.store'), $invalidMurojaah);
        $responseInvalid->assertSessionHasErrors(['juz', 'nilai']);
    }
}
