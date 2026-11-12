<?php

namespace Tests\Feature;

use App\Models\KegiatanPondok;
use App\Models\Pendaftaran;
use App\Models\PresensiKegiatan;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_16_domain_tables_exist(): void
    {
        $expectedTables = [
            // Academic tables (9)
            'users',
            'santri',
            'kegiatan_pondok',
            'ustadz_kegiatan',
            'ustadz_santri',
            'presensi_kegiatan',
            'presensi_santri',
            'setoran_hafalan',
            'murojaah',
            // PPDB & CMS tables (7)
            'pendaftarans',
            'status_forms',
            'informasis',
            'kontaks',
            'statistiks',
            'carousels',
            'keunggulans',
            // Spatie tables
            'roles',
            'permissions',
            'model_has_roles',
            'model_has_permissions',
            'role_has_permissions',
        ];

        foreach ($expectedTables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Failed asserting that table [{$table}] exists."
            );
        }
    }

    public function test_santri_table_columns_and_nis_string_compatibility(): void
    {
        $this->assertTrue(Schema::hasColumns('santri', [
            'id', 'nama', 'nis', 'tingkatan', 'kelas', 'wali_murid_id', 'tanggal_lahir', 'created_at', 'updated_at',
        ]));

        $wali = User::create([
            'name' => 'Wali Test',
            'email' => 'wali_schema@test.com',
            'password' => Hash::make('password'),
        ]);

        $santri = Santri::create([
            'nama' => 'Santri Alphanumeric NIS',
            'nis' => '2026001',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
            'wali_murid_id' => $wali->id,
            'tanggal_lahir' => '2012-01-01',
        ]);

        $this->assertDatabaseHas('santri', [
            'id' => $santri->id,
            'nis' => '2026001',
        ]);

        // Assert duplicate NIS throws QueryException
        $this->expectException(QueryException::class);
        Santri::create([
            'nama' => 'Santri Duplicate NIS',
            'nis' => '2026001',
            'tingkatan' => 'tsanawiyah',
        ]);
    }

    public function test_pendaftarans_table_has_all_41_canonical_fields_plus_santri_id(): void
    {
        $expectedColumns = [
            'id',
            'user_id',
            'nama_lengkap',
            'jenis_kelamin',
            'email',
            'no_hp',
            'alamat',
            'tempat_lahir',
            'tanggal_lahir',
            'nisn',
            'nik_santri',
            'no_kk',
            'nama_ayah',
            'nik_ayah',
            'pekerjaan_ayah',
            'penghasilan_ayah',
            'no_hp_ayah',
            'nama_ibu',
            'nik_ibu',
            'pekerjaan_ibu',
            'penghasilan_ibu',
            'no_hp_ibu',
            'asal_sekolah',
            'alamat_asal_sekolah',
            'jumlah_hafalan',
            'prestasi',
            'pas_photo',
            'bukti_transfer',
            'formulir_pendaftaran',
            'surat_pernyataan',
            'akta_kelahiran',
            'kartu_keluarga',
            'ijazah_terakhir',
            'raport_terakhir',
            'skhun',
            'sktm',
            'berkas',
            'status',
            'tanggal_daftar',
            'santri_id',
            'created_at',
            'updated_at',
        ];

        $this->assertTrue(
            Schema::hasColumns('pendaftarans', $expectedColumns),
            'Table [pendaftarans] is missing some of the 41 canonical columns or santri_id.'
        );
    }

    public function test_composite_unique_constraints_enforced(): void
    {
        $ustadz = User::create([
            'name' => 'Ustadz Test',
            'email' => 'ustadz_schema@test.com',
            'password' => Hash::make('password'),
        ]);

        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Tahfidz Sore',
            'tingkatan' => 'semua',
        ]);

        $santri = Santri::create([
            'nama' => 'Santri Pivot Test',
            'nis' => 'NIS-TEST-01',
            'tingkatan' => 'tsanawiyah',
        ]);

        // 1. Test ustadz_kegiatan composite unique
        DB::table('ustadz_kegiatan')->insert([
            'user_id' => $ustadz->id,
            'kegiatan_pondok_id' => $kegiatan->id,
            'created_at' => now(),
        ]);

        try {
            DB::table('ustadz_kegiatan')->insert([
                'user_id' => $ustadz->id,
                'kegiatan_pondok_id' => $kegiatan->id,
                'created_at' => now(),
            ]);
            $this->fail('Expected QueryException for duplicate ustadz_kegiatan');
        } catch (QueryException $e) {
            $this->assertTrue(true);
        }

        // 2. Test ustadz_santri composite unique
        DB::table('ustadz_santri')->insert([
            'user_id' => $ustadz->id,
            'santri_id' => $santri->id,
            'created_at' => now(),
        ]);

        try {
            DB::table('ustadz_santri')->insert([
                'user_id' => $ustadz->id,
                'santri_id' => $santri->id,
                'created_at' => now(),
            ]);
            $this->fail('Expected QueryException for duplicate ustadz_santri');
        } catch (QueryException $e) {
            $this->assertTrue(true);
        }

        // 3. Test presensi_kegiatan composite unique (kegiatan, ustadz, tanggal)
        PresensiKegiatan::create([
            'kegiatan_pondok_id' => $kegiatan->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-12',
            'hari' => 'Kamis',
            'jam_mulai' => '06:00:00',
            'status' => 'valid',
        ]);

        try {
            PresensiKegiatan::create([
                'kegiatan_pondok_id' => $kegiatan->id,
                'ustadz_id' => $ustadz->id,
                'tanggal' => '2026-11-12',
                'hari' => 'Kamis',
                'jam_mulai' => '07:00:00',
                'status' => 'valid',
            ]);
            $this->fail('Expected QueryException for duplicate presensi_kegiatan on same date');
        } catch (QueryException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_foreign_key_delete_behaviors(): void
    {
        $wali = User::create([
            'name' => 'Wali Delete Test',
            'email' => 'wali_delete@test.com',
            'password' => Hash::make('password'),
        ]);

        $santri = Santri::create([
            'nama' => 'Santri Wali Delete',
            'nis' => 'NIS-WALI-DEL',
            'tingkatan' => 'tsanawiyah',
            'wali_murid_id' => $wali->id,
        ]);

        $userApplicant = User::create([
            'name' => 'Applicant Test',
            'email' => 'applicant@test.com',
            'password' => Hash::make('password'),
        ]);

        $pendaftaran = Pendaftaran::create([
            'user_id' => $userApplicant->id,
            'nama_lengkap' => 'Applicant Full Name',
            'jenis_kelamin' => 'Laki-laki',
            'email' => 'applicant@test.com',
            'no_hp' => '08123456789',
            'alamat' => 'Alamat Applicant',
            'santri_id' => $santri->id,
        ]);

        // When Wali is deleted, santri.wali_murid_id should become NULL
        $wali->delete();
        $this->assertDatabaseHas('santri', [
            'id' => $santri->id,
            'wali_murid_id' => null,
        ]);

        // When Santri is deleted, pendaftarans.santri_id should become NULL
        $santri->delete();
        $this->assertDatabaseHas('pendaftarans', [
            'id' => $pendaftaran->id,
            'santri_id' => null,
        ]);

        // When UserApplicant is deleted, pendaftaran should be cascade deleted
        $userApplicant->delete();
        $this->assertDatabaseMissing('pendaftarans', [
            'id' => $pendaftaran->id,
        ]);
    }
}
