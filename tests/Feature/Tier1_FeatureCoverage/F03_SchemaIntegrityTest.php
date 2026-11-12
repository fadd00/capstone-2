<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class F03_SchemaIntegrityTest extends TestCase
{
    /**
     * 1. All 16 domain tables must exist in the database schema.
     */
    public function test_all_sixteen_domain_tables_exist_in_database(): void
    {
        $domainTables = [
            // CP1 Academic Tables (9)
            'users',
            'santri',
            'kegiatan_pondok',
            'ustadz_kegiatan',
            'ustadz_santri',
            'presensi_kegiatan',
            'presensi_santri',
            'setoran_hafalan',
            'murojaah',
            // PPDB & CMS Tables (7)
            'pendaftarans',
            'status_forms',
            'informasis',
            'kontaks',
            'statistiks',
            'carousels',
            'keunggulans',
        ];

        foreach ($domainTables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Database schema must contain domain table: '{$table}'"
            );
        }
    }

    /**
     * 2. The 'users' and Spatie authorization tables must contain required columns.
     */
    public function test_users_and_spatie_tables_columns_exist(): void
    {
        $userColumns = ['id', 'name', 'email', 'password', 'created_at', 'updated_at'];
        foreach ($userColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('users', $column),
                "Table 'users' must contain column '{$column}'"
            );
        }

        $this->assertTrue(Schema::hasTable('roles'), "Spatie 'roles' table must exist.");
        $this->assertTrue(Schema::hasTable('model_has_roles'), "Spatie 'model_has_roles' table must exist.");
    }

    /**
     * 3. The 'santri' table must contain all domain columns including 'nis' and 'wali_murid_id'.
     */
    public function test_santri_table_columns_and_nis_column(): void
    {
        $santriColumns = [
            'id',
            'nama',
            'nis',
            'tingkatan',
            'kelas',
            'wali_murid_id',
            'tanggal_lahir',
            'created_at',
            'updated_at',
        ];

        foreach ($santriColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('santri', $column),
                "Table 'santri' must contain column '{$column}'"
            );
        }
    }

    /**
     * 4. The 'pendaftarans' table must contain the full PPDB specification fields including 10 file paths.
     */
    public function test_pendaftarans_table_has_all_required_fields_and_upload_paths(): void
    {
        $requiredPendaftaranColumns = [
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
            'nama_ibu',
            'nik_ibu',
            'asal_sekolah',
            'pas_photo',
            'bukti_transfer',
            'formulir_pendaftaran',
            'surat_pernyataan',
            'akta_kelahiran',
            'kartu_keluarga',
            'raport_terakhir',
            'ijazah_terakhir',
            'skhun',
            'sktm',
            'status',
            'santri_id',
            'created_at',
            'updated_at',
        ];

        foreach ($requiredPendaftaranColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('pendaftarans', $column),
                "Table 'pendaftarans' must contain column '{$column}'"
            );
        }
    }

    /**
     * 5. Presensi and Tahfidz tables must contain necessary foreign keys and data columns.
     */
    public function test_presensi_and_tahfidz_tables_columns_exist(): void
    {
        // presensi_kegiatan
        $this->assertTrue(Schema::hasColumn('presensi_kegiatan', 'kegiatan_pondok_id'));
        $this->assertTrue(Schema::hasColumn('presensi_kegiatan', 'ustadz_id'));
        $this->assertTrue(Schema::hasColumn('presensi_kegiatan', 'tanggal'));
        $this->assertTrue(Schema::hasColumn('presensi_kegiatan', 'latitude'));
        $this->assertTrue(Schema::hasColumn('presensi_kegiatan', 'longitude'));
        $this->assertTrue(Schema::hasColumn('presensi_kegiatan', 'status'));

        // presensi_santri
        $this->assertTrue(Schema::hasColumn('presensi_santri', 'presensi_kegiatan_id'));
        $this->assertTrue(Schema::hasColumn('presensi_santri', 'santri_id'));
        $this->assertTrue(Schema::hasColumn('presensi_santri', 'status'));

        // setoran_hafalan
        $this->assertTrue(Schema::hasColumn('setoran_hafalan', 'santri_id'));
        $this->assertTrue(Schema::hasColumn('setoran_hafalan', 'ustadz_id'));
        $this->assertTrue(Schema::hasColumn('setoran_hafalan', 'juz'));
        $this->assertTrue(Schema::hasColumn('setoran_hafalan', 'surat'));
        $this->assertTrue(Schema::hasColumn('setoran_hafalan', 'ayat'));
        $this->assertTrue(Schema::hasColumn('setoran_hafalan', 'nilai'));
        $this->assertTrue(Schema::hasColumn('setoran_hafalan', 'status_selesai'));

        // murojaah
        $this->assertTrue(Schema::hasColumn('murojaah', 'santri_id'));
        $this->assertTrue(Schema::hasColumn('murojaah', 'ustadz_id'));
        $this->assertTrue(Schema::hasColumn('murojaah', 'juz'));
        $this->assertTrue(Schema::hasColumn('murojaah', 'surat'));
        $this->assertTrue(Schema::hasColumn('murojaah', 'status_selesai'));
    }

    /**
     * 6. Landing CMS and support tables must contain required columns.
     */
    public function test_cms_and_landing_page_tables_columns_exist(): void
    {
        // status_forms
        $this->assertTrue(Schema::hasColumn('status_forms', 'status'));
        $this->assertTrue(Schema::hasColumn('status_forms', 'tanggal_buka'));
        $this->assertTrue(Schema::hasColumn('status_forms', 'tanggal_tutup'));

        // informasis
        $this->assertTrue(Schema::hasColumn('informasis', 'judul'));
        $this->assertTrue(Schema::hasColumn('informasis', 'isi'));

        // kontaks
        $this->assertTrue(Schema::hasColumn('kontaks', 'alamat'));
        $this->assertTrue(Schema::hasColumn('kontaks', 'telepon'));
        $this->assertTrue(Schema::hasColumn('kontaks', 'email'));

        // statistiks
        $this->assertTrue(Schema::hasColumn('statistiks', 'santri_aktif'));
        $this->assertTrue(Schema::hasColumn('statistiks', 'asatidzah_bersanad'));
        $this->assertTrue(Schema::hasColumn('statistiks', 'tahun_berkhidmat'));

        // carousels
        $this->assertTrue(Schema::hasColumn('carousels', 'image'));
        $this->assertTrue(Schema::hasColumn('carousels', 'order'));

        // keunggulans
        $this->assertTrue(Schema::hasColumn('keunggulans', 'judul'));
        $this->assertTrue(Schema::hasColumn('keunggulans', 'deskripsi_singkat'));
        $this->assertTrue(Schema::hasColumn('keunggulans', 'deskripsi_detail'));
    }
}
