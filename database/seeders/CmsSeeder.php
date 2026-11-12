<?php

namespace Database\Seeders;

use App\Models\Carousel;
use App\Models\Informasi;
use App\Models\Keunggulan;
use App\Models\Kontak;
use App\Models\Statistik;
use App\Models\StatusForm;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Status Form Pendaftaran (Buka secara default dengan rentang waktu aktif)
        StatusForm::updateOrCreate(
            ['id' => 1],
            [
                'status' => 'Buka',
                'tanggal_buka' => Carbon::now()->subDays(30)->toDateString(),
                'tanggal_tutup' => Carbon::now()->addDays(90)->toDateString(),
            ]
        );

        // 2. Statistik Counter (dokum-ppdb §4.2 F & §4.3)
        Statistik::updateOrCreate(
            ['id' => 1],
            [
                'santri_aktif' => 110,
                'asatidzah_bersanad' => 30,
                'tahun_berkhidmat' => 13,
            ]
        );

        // 3. Kontak Resmi Lembaga (dokum-ppdb §4.2 E)
        Kontak::updateOrCreate(
            ['id' => 1],
            [
                'alamat' => 'Jl. Imogiri Timur KM 7.5, Grojogan, Wirokerten, Banguntapan, Bantul, D.I. Yogyakarta 55194',
                'telepon' => '081234567890',
                'email' => 'official@pptq.sch.id',
            ]
        );

        // 4. Banner Carousel
        $carousels = [
            [
                'id' => 1,
                'image' => 'carousel/banner-1.jpg',
                'caption' => 'Penerimaan Peserta Didik Baru (PPDB) PPTQ Muhammadiyah Ibnu Juraimi',
                'order' => 1,
            ],
            [
                'id' => 2,
                'image' => 'carousel/banner-2.jpg',
                'caption' => 'Mencetak Generasi Qurani yang Berakhlak Mulia & Mandiri',
                'order' => 2,
            ],
            [
                'id' => 3,
                'image' => 'carousel/banner-3.jpg',
                'caption' => 'Pendidikan Tahfidz Berstandar Sanad & Berbasis Akhlakul Karimah',
                'order' => 3,
            ],
        ];
        foreach ($carousels as $c) {
            Carousel::updateOrCreate(['id' => $c['id']], $c);
        }

        // 5. Keunggulan Pesantren (dokum-ppdb §4.2 H)
        $keunggulans = [
            [
                'id' => 1,
                'judul' => 'Tahfidz Al-Qur\'an Bersanad',
                'deskripsi_singkat' => 'Bimbingan intensif hafalan 30 Juz bersanad muttashil.',
                'deskripsi_detail' => 'Santri dibimbing intensif oleh para asatidzah hafizh/hafizhah bersanad hingga Rasulullah SAW.',
                'order' => 1,
            ],
            [
                'id' => 2,
                'judul' => 'Kurikulum Terpadu & Berkarakter',
                'deskripsi_singkat' => 'Integrasi kurikulum kepesantrenan dan pendidikan formal.',
                'deskripsi_detail' => 'Memadukan kurikulum salaf dan kurikulum nasional demi melahirkan santri unggul berakhlak mulia.',
                'order' => 2,
            ],
            [
                'id' => 3,
                'judul' => 'Lingkungan Asri & Fasilitas Lengkap',
                'deskripsi_singkat' => 'Fasilitas asrama representatif dan lingkungan kondusif.',
                'deskripsi_detail' => 'Suasana belajar yang aman, nyaman, dan asri mendukung konsentrasi menghafal Al-Qur\'an.',
                'order' => 3,
            ],
        ];
        foreach ($keunggulans as $k) {
            Keunggulan::updateOrCreate(['id' => $k['id']], $k);
        }

        // 6. Pengumuman / Informasi (dokum-ppdb §4.2 D)
        $informasis = [
            [
                'id' => 1,
                'judul' => 'Penerimaan Santri Baru Gelombang 1 Resmi Dibuka',
                'isi' => 'Pendaftaran online PPDB PPTQ Ibnu Juraimi Gelombang 1 telah dibuka. Silakan mengisi formulir pendaftaran dan mengunggah 10 berkas persyaratan legal secara lengkap.',
            ],
            [
                'id' => 2,
                'judul' => 'Informasi Pelaksanaan Tes Seleksi Masuk',
                'isi' => 'Tes seleksi masuk santri baru meliputi uji kemampuan membaca Al-Qur\'an, tes tertulis dasar, dan wawancara orang tua/wali santri.',
            ],
        ];
        foreach ($informasis as $info) {
            Informasi::updateOrCreate(['id' => $info['id']], $info);
        }
    }
}
