<?php

namespace Tests\Support;

use App\Models\Pendaftaran;
use App\Models\StatusForm;
use App\Models\User;
use Illuminate\Http\UploadedFile;

trait CreatesPpdbFixtures
{
    use CreatesTestUsers;

    /**
     * Generate 10 fake uploaded files matching the PPDB multi-upload specifications.
     * 7 required and 3 optional files.
     *
     * @param  array<string, UploadedFile>  $overrides
     * @return array<string, UploadedFile>
     */
    protected function fakePpdbFiles(array $overrides = []): array
    {
        $defaults = [
            'pas_photo' => UploadedFile::fake()->image('pas_photo.jpg', 600, 800),
            'bukti_transfer' => UploadedFile::fake()->create('bukti_transfer.pdf', 300, 'application/pdf'),
            'formulir_pendaftaran' => UploadedFile::fake()->create('formulir_pendaftaran.pdf', 400, 'application/pdf'),
            'surat_pernyataan' => UploadedFile::fake()->create('surat_pernyataan.pdf', 350, 'application/pdf'),
            'akta_kelahiran' => UploadedFile::fake()->create('akta_kelahiran.pdf', 500, 'application/pdf'),
            'kartu_keluarga' => UploadedFile::fake()->create('kartu_keluarga.pdf', 450, 'application/pdf'),
            'raport_terakhir' => UploadedFile::fake()->create('raport_terakhir.pdf', 600, 'application/pdf'),
            'ijazah_terakhir' => UploadedFile::fake()->create('ijazah_terakhir.pdf', 550, 'application/pdf'),
            'skhun' => UploadedFile::fake()->create('skhun.pdf', 300, 'application/pdf'),
            'sktm' => UploadedFile::fake()->create('sktm.pdf', 250, 'application/pdf'),
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Generate the 7 mandatory uploaded files.
     *
     * @param  array<string, UploadedFile>  $overrides
     * @return array<string, UploadedFile>
     */
    protected function fakeRequiredPpdbFiles(array $overrides = []): array
    {
        $defaults = [
            'pas_photo' => UploadedFile::fake()->image('pas_photo.jpg', 600, 800),
            'bukti_transfer' => UploadedFile::fake()->create('bukti_transfer.pdf', 300, 'application/pdf'),
            'formulir_pendaftaran' => UploadedFile::fake()->create('formulir_pendaftaran.pdf', 400, 'application/pdf'),
            'surat_pernyataan' => UploadedFile::fake()->create('surat_pernyataan.pdf', 350, 'application/pdf'),
            'akta_kelahiran' => UploadedFile::fake()->create('akta_kelahiran.pdf', 500, 'application/pdf'),
            'kartu_keluarga' => UploadedFile::fake()->create('kartu_keluarga.pdf', 450, 'application/pdf'),
            'raport_terakhir' => UploadedFile::fake()->create('raport_terakhir.pdf', 600, 'application/pdf'),
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Provide a valid payload for PPDB online registration form submission.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    protected function validPpdbFormData(array $attributes = []): array
    {
        $defaults = [
            'nama_lengkap' => 'Muhammad Rifqi Pratama',
            'jenis_kelamin' => 'Laki-laki',
            'email' => 'calon_rifqi_'.uniqid().'@example.com',
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Kaliurang KM 9, Sleman, D.I. Yogyakarta',
            'tempat_lahir' => 'Sleman',
            'tanggal_lahir' => '2012-05-15',
            'nisn' => '0012345678',
            'nik_santri' => '3404011505120001',
            'no_kk' => '3404011202020002',
            'nama_ayah' => 'Bambang Supriyanto',
            'nik_ayah' => '3404010101750003',
            'pekerjaan_ayah' => 'PNS',
            'penghasilan_ayah' => 'Rp 5.000.000 - Rp 10.000.000',
            'no_hp_ayah' => '081398765432',
            'nama_ibu' => 'Siti Nurhaliza',
            'nik_ibu' => '3404010202800004',
            'pekerjaan_ibu' => 'Guru',
            'penghasilan_ibu' => 'Rp 3.000.000 - Rp 5.000.000',
            'no_hp_ibu' => '081387654321',
            'asal_sekolah' => 'SDIT Luqman Al Hakim',
            'alamat_asal_sekolah' => 'Jl. Palagan Tentara Pelajar KM 7',
            'jumlah_hafalan' => '3 Juz',
            'prestasi' => 'Juara 1 MHQ Tingkat Kabupaten',
        ];

        return array_merge($defaults, $attributes);
    }

    /**
     * Create and persist a Pendaftaran record in the database.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function createPendaftaran(array $attributes = [], ?User $user = null): Pendaftaran
    {
        if (! $user) {
            $user = $this->createCalonSantri([
                'name' => $attributes['nama_lengkap'] ?? 'Calon Santri Terdaftar',
                'email' => $attributes['email'] ?? ('calon_'.uniqid().'@ibnujuraimi.sch.id'),
            ]);
        }

        $pendaftaranData = array_merge([
            'user_id' => $user->id,
            'nama_lengkap' => $user->name,
            'jenis_kelamin' => 'Laki-laki',
            'email' => $user->email,
            'no_hp' => '081234567890',
            'alamat' => 'Yogyakarta',
            'tempat_lahir' => 'Yogyakarta',
            'tanggal_lahir' => '2012-01-01',
            'nisn' => '0012345678',
            'nik_santri' => '3404010101120001',
            'no_kk' => '3404010101900001',
            'nama_ayah' => 'Ayah Calon',
            'nik_ayah' => '3404010101700001',
            'pekerjaan_ayah' => 'Wiraswasta',
            'penghasilan_ayah' => 'Rp 5.000.000',
            'no_hp_ayah' => '081200000001',
            'nama_ibu' => 'Ibu Calon',
            'nik_ibu' => '3404010101750001',
            'pekerjaan_ibu' => 'Ibu Rumah Tangga',
            'penghasilan_ibu' => '-',
            'no_hp_ibu' => '081200000002',
            'asal_sekolah' => 'SDIT Yogyakarta',
            'alamat_asal_sekolah' => 'Jl. Malioboro',
            'jumlah_hafalan' => '2 Juz',
            'prestasi' => 'Juara Tahfidz',
            'pas_photo' => 'pendaftaran/pas_photo/default.jpg',
            'bukti_transfer' => 'pendaftaran/bukti_transfer/default.pdf',
            'formulir_pendaftaran' => 'pendaftaran/formulir_pendaftaran/default.pdf',
            'surat_pernyataan' => 'pendaftaran/surat_pernyataan/default.pdf',
            'akta_kelahiran' => 'pendaftaran/akta_kelahiran/default.pdf',
            'kartu_keluarga' => 'pendaftaran/kartu_keluarga/default.pdf',
            'raport_terakhir' => 'pendaftaran/raport_terakhir/default.pdf',
            'status' => 'Menunggu',
            'santri_id' => null,
        ], $attributes);

        return Pendaftaran::create($pendaftaranData);
    }

    /**
     * Create and persist a StatusForm configuration record.
     *
     * @param  string  $status  'Buka' or 'Tutup'
     * @param  string|null  $buka  'YYYY-MM-DD'
     * @param  string|null  $tutup  'YYYY-MM-DD'
     */
    protected function createStatusForm(string $status = 'Buka', ?string $buka = null, ?string $tutup = null): StatusForm
    {
        return StatusForm::create([
            'status' => $status,
            'tanggal_buka' => $buka,
            'tanggal_tutup' => $tutup,
        ]);
    }
}
