<?php

namespace Tests\Unit;

use App\Models\Santri;
use App\Models\User;
use App\Services\PendaftarPromotionService;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

class PendaftarPromotionServiceTest extends TestCase
{
    use CreatesPpdbFixtures;
    use CreatesTestUsers;

    protected PendaftarPromotionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PendaftarPromotionService;
    }

    /**
     * 1. Successfully promote a pendaftar with status 'Lulus' to an active Santri record.
     */
    public function test_promote_successful_for_verified_lulus_pendaftar(): void
    {
        $calon = $this->createCalonSantri(['name' => 'Fauzan Ahmad']);
        $pendaftar = $this->createPendaftaran([
            'nama_lengkap' => 'Fauzan Ahmad',
            'status' => 'Lulus',
            'tanggal_lahir' => '2012-08-20',
        ], $calon);

        $nis = '2026001';
        $tingkatan = 'tsanawiyah';
        $kelas = '7A';

        $santri = $this->service->promote($pendaftar, $nis, $tingkatan, $kelas, null);

        $this->assertInstanceOf(Santri::class, $santri);
        $this->assertEquals('Fauzan Ahmad', $santri->nama);
        $this->assertEquals($nis, $santri->nis);
        $this->assertEquals($tingkatan, $santri->tingkatan);
        $this->assertEquals($kelas, $santri->kelas);
        $this->assertNull($santri->wali_murid_id);
        $this->assertEquals('2012-08-20', $santri->tanggal_lahir?->format('Y-m-d') ?? $santri->tanggal_lahir);

        // Assert the pendaftar record is updated with santri_id
        $pendaftar->refresh();
        $this->assertEquals($santri->id, $pendaftar->santri_id);
    }

    /**
     * 2. Cannot promote a pendaftar whose status is NOT 'Lulus' (e.g. 'Menunggu' or 'Tidak Lulus').
     */
    public function test_promote_throws_exception_if_pendaftar_status_is_not_lulus(): void
    {
        $pendaftarMenunggu = $this->createPendaftaran([
            'nama_lengkap' => 'Santri Belum Lulus',
            'status' => 'Menunggu',
        ]);

        $this->expectException(\Throwable::class);

        $this->service->promote($pendaftarMenunggu, '2026002', 'tsanawiyah', '7B', null);
    }

    /**
     * 3. Cannot promote a pendaftar who has already been promoted (santri_id already exists).
     */
    public function test_promote_throws_exception_if_pendaftar_already_has_santri_id(): void
    {
        $calon = $this->createCalonSantri();
        $existingSantri = Santri::create([
            'nama' => 'Santri Sebelumnya',
            'nis' => '2026003',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7A',
        ]);

        $pendaftarSudahPromosi = $this->createPendaftaran([
            'nama_lengkap' => 'Santri Sebelumnya',
            'status' => 'Lulus',
            'santri_id' => $existingSantri->id,
        ], $calon);

        $this->expectException(\Throwable::class);

        $this->service->promote($pendaftarSudahPromosi, '2026004', 'tsanawiyah', '7A', null);
    }

    /**
     * 4. Successfully assigns an existing Wali Murid user ID when provided.
     */
    public function test_promote_links_optional_wali_murid_id_correctly(): void
    {
        $wali = $this->createWaliMurid(['name' => 'Bapak Hartono']);
        $pendaftar = $this->createPendaftaran([
            'nama_lengkap' => 'Anak Hartono',
            'status' => 'Lulus',
        ]);

        $santri = $this->service->promote($pendaftar, '2026005', 'aliyah', '10 IPA', $wali->id);

        $this->assertEquals($wali->id, $santri->wali_murid_id);
    }

    /**
     * 5. Supports 7-digit numeric NIS format and alphanumeric strings in VARCHAR(50).
     */
    public function test_promote_accepts_and_preserves_7_digit_numeric_and_alphanumeric_nis(): void
    {
        $pendaftarA = $this->createPendaftaran(['nama_lengkap' => 'Santri Alpha', 'status' => 'Lulus']);
        $pendaftarB = $this->createPendaftaran(['nama_lengkap' => 'Santri Beta', 'status' => 'Lulus']);

        $santriA = $this->service->promote($pendaftarA, '2026099', 'tsanawiyah', '7A', null);
        $santriB = $this->service->promote($pendaftarB, 'PPTQ-2026-X1', 'takhassus', 'Tahfidz 1', null);

        $this->assertEquals('2026099', $santriA->nis);
        $this->assertEquals('PPTQ-2026-X1', $santriB->nis);
    }

    /**
     * 6. Duplicate NIS triggers database or domain validation failure.
     */
    public function test_promote_with_duplicate_nis_fails_uniqueness(): void
    {
        $pendaftar1 = $this->createPendaftaran(['nama_lengkap' => 'Santri Satu', 'status' => 'Lulus']);
        $pendaftar2 = $this->createPendaftaran(['nama_lengkap' => 'Santri Dua', 'status' => 'Lulus']);

        $this->service->promote($pendaftar1, '2026010', 'tsanawiyah', '7A', null);

        $this->expectException(\Throwable::class);
        $this->service->promote($pendaftar2, '2026010', 'tsanawiyah', '7B', null);
    }
}
