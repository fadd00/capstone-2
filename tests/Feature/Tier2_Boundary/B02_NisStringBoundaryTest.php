<?php

namespace Tests\Feature\Tier2_Boundary;

use App\Models\Santri;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Tier 2 Boundary Test: B02 NIS String Boundary
 *
 * Source: dokum-cp1 §5.2 Q-ADM-06, Build Plan §4.1, §19 Change Log v2.0
 * Verifies NIS boundary constraints: 7-digit numeric string ('2026001'),
 * maximum 50 characters, 51 characters rejected, alphanumeric patterns,
 * uniqueness collision rejection, and empty/whitespace handling.
 */
class B02_NisStringBoundaryTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    /**
     * 1. Standard 7-digit numeric NIS format (e.g. '2026001') is accepted by store request.
     */
    public function test_standard_seven_digit_numeric_nis_is_accepted(): void
    {
        $admin = $this->createAdmin();

        $payload = [
            'nama' => 'Santri Tujuh Digit',
            'nis' => '2026001',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7A',
            'tanggal_lahir' => '2012-01-01',
        ];

        $response = $this->actingAs($admin)->post(route('admin.santri.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('santri', [
            'nama' => 'Santri Tujuh Digit',
            'nis' => '2026001',
            'tingkatan' => 'tsanawiyah',
        ]);
    }

    /**
     * 2. Exactly 50-character NIS string (boundary upper limit) is accepted by schema and validation.
     */
    public function test_maximum_50_character_nis_string_boundary_is_accepted(): void
    {
        $admin = $this->createAdmin();
        $exact50CharsNis = str_repeat('A', 50);

        $payload = [
            'nama' => 'Santri Batas Maksimal 50 Karakter',
            'nis' => $exact50CharsNis,
            'tingkatan' => 'aliyah',
            'kelas' => '10 IPA',
            'tanggal_lahir' => '2010-06-15',
        ];

        $response = $this->actingAs($admin)->post(route('admin.santri.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('santri', [
            'nis' => $exact50CharsNis,
        ]);
    }

    /**
     * 3. Exceeding 50 characters (e.g. 51 chars) is rejected by validation with session error.
     */
    public function test_exceeding_50_character_nis_string_is_rejected(): void
    {
        $admin = $this->createAdmin();
        $exceeding51CharsNis = str_repeat('B', 51);

        $payload = [
            'nama' => 'Santri Kelebihan Karakter',
            'nis' => $exceeding51CharsNis,
            'tingkatan' => 'aliyah',
            'kelas' => '10 IPA',
        ];

        $response = $this->actingAs($admin)->post(route('admin.santri.store'), $payload);

        $response->assertSessionHasErrors(['nis']);
        $this->assertDatabaseMissing('santri', ['nis' => $exceeding51CharsNis]);
    }

    /**
     * 4. Alphanumeric and formatted NIS with symbols (hyphens, slashes) is accepted.
     */
    public function test_alphanumeric_and_symbolic_nis_strings_are_accepted(): void
    {
        $admin = $this->createAdmin();

        $testCases = [
            'PPTQ-2026/001',
            'TSA.2026-X',
            'A123456789',
        ];

        foreach ($testCases as $index => $customNis) {
            $payload = [
                'nama' => "Santri Format Khusus {$index}",
                'nis' => $customNis,
                'tingkatan' => 'takhassus',
                'kelas' => '1',
            ];

            $response = $this->actingAs($admin)->post(route('admin.santri.store'), $payload);
            $response->assertSessionHasNoErrors();
            $this->assertDatabaseHas('santri', ['nis' => $customNis]);
        }
    }

    /**
     * 5. Duplicate NIS is rejected by HTTP validation and triggers unique constraint in DB.
     */
    public function test_duplicate_nis_string_is_rejected_by_validation_and_database_unique_index(): void
    {
        $admin = $this->createAdmin();

        Santri::create([
            'nama' => 'Santri Pertama',
            'nis' => 'DUPLICATE-2026',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);

        $payload = [
            'nama' => 'Santri Kedua Mencoba NIS Sama',
            'nis' => 'DUPLICATE-2026',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '8',
        ];

        $response = $this->actingAs($admin)->post(route('admin.santri.store'), $payload);

        $response->assertSessionHasErrors(['nis']);

        // Assert database-level unique constraint prevents manual insert as well
        $this->expectException(QueryException::class);
        Santri::create([
            'nama' => 'Santri Ketiga Bypass Validation',
            'nis' => 'DUPLICATE-2026',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '9',
        ]);
    }

    /**
     * 6. Empty, whitespace-only, or missing NIS is rejected.
     */
    public function test_empty_or_whitespace_only_nis_is_rejected(): void
    {
        $admin = $this->createAdmin();

        $invalidPayloads = [
            'empty string' => ['nis' => ''],
            'whitespace only' => ['nis' => '   '],
        ];

        foreach ($invalidPayloads as $desc => $override) {
            $payload = array_merge([
                'nama' => 'Santri Invalid NIS',
                'tingkatan' => 'tsanawiyah',
                'kelas' => '7',
            ], $override);

            $response = $this->actingAs($admin)->post(route('admin.santri.store'), $payload);
            $response->assertSessionHasErrors(['nis']);
        }
    }
}
