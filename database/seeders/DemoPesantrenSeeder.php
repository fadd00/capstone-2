<?php

namespace Database\Seeders;

use App\Models\KegiatanPondok;
use App\Models\Murojaah;
use App\Models\PresensiKegiatan;
use App\Models\PresensiSantri;
use App\Models\Santri;
use App\Models\SetoranHafalan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoPesantrenSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Asatidzah Accounts
        $asatidzahData = [
            ['name' => 'Ustadz Abdullah Faqih', 'email' => 'ustadz1@pptq.test'],
            ['name' => 'Ustadz Muhammad Ridwan', 'email' => 'ustadz2@pptq.test'],
            ['name' => 'Ustadz Hasan Al-Basri', 'email' => 'ustadz3@pptq.test'],
            ['name' => 'Ustadz Imam Syafi\'i', 'email' => 'ustadz4@pptq.test'],
            ['name' => 'Ustadz Zainul Arifin', 'email' => 'ustadz5@pptq.test'],
        ];

        $asatidzah = [];
        foreach ($asatidzahData as $data) {
            $u = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $u->syncRoles(['ustadz']);
            $asatidzah[$data['email']] = $u;
        }

        // 2. Wali Murid Accounts
        $waliData = [
            ['name' => 'Bapak Ahmad Sulaiman', 'email' => 'wali1@pptq.test'],
            ['name' => 'Ibu Siti Aminah', 'email' => 'wali2@pptq.test'],
        ];

        $walis = [];
        foreach ($waliData as $data) {
            $w = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $w->syncRoles(['wali_murid']);
            $walis[$data['email']] = $w;
        }

        // 3. Kegiatan Pondok
        $kegiatanPagi = KegiatanPondok::updateOrCreate(
            ['nama_kegiatan' => 'Tahfidz Pagi'],
            [
                'deskripsi' => 'Hafalan rutin bada subuh',
                'tingkatan' => 'tsanawiyah',
                'kelas' => '7',
            ]
        );

        $kegiatanIntensif = KegiatanPondok::updateOrCreate(
            ['nama_kegiatan' => 'Tahfidz Al-Qur\'an Intensif'],
            [
                'deskripsi' => 'Hafalan dan perbaikan makhraj',
                'tingkatan' => 'semua',
                'kelas' => null,
            ]
        );

        $kegiatanFiqih = KegiatanPondok::updateOrCreate(
            ['nama_kegiatan' => 'Kajian Fiqih Taharah'],
            [
                'deskripsi' => 'Kajian fiqih bersuci',
                'tingkatan' => 'semua',
                'kelas' => null,
            ]
        );

        $kegiatanKitab = KegiatanPondok::updateOrCreate(
            ['nama_kegiatan' => 'Kajian Kitab Kuning'],
            [
                'deskripsi' => 'Kajian kitab Fathul Qorib',
                'tingkatan' => 'aliyah',
                'kelas' => '10',
            ]
        );

        // 4. Santri Master
        $santri1 = Santri::updateOrCreate(
            ['nis' => '2026001'],
            [
                'nama' => 'Muhammad Hafizh',
                'tingkatan' => 'tsanawiyah',
                'kelas' => '7',
                'wali_murid_id' => $walis['wali1@pptq.test']->id,
                'tanggal_lahir' => '2012-05-14',
            ]
        );

        $santri2 = Santri::updateOrCreate(
            ['nis' => '2026002'],
            [
                'nama' => 'Ahmad Fauzi',
                'tingkatan' => 'tsanawiyah',
                'kelas' => '7',
                'wali_murid_id' => $walis['wali1@pptq.test']->id,
                'tanggal_lahir' => '2012-08-20',
            ]
        );

        $santri3 = Santri::updateOrCreate(
            ['nis' => '2026003'],
            [
                'nama' => 'Fatimah Az-Zahra',
                'tingkatan' => 'aliyah',
                'kelas' => '10',
                'wali_murid_id' => $walis['wali2@pptq.test']->id,
                'tanggal_lahir' => '2009-03-12',
            ]
        );

        $santri4 = Santri::updateOrCreate(
            ['nis' => '2026004'],
            [
                'nama' => 'Zaid bin Tsabit',
                'tingkatan' => 'takhassus',
                'kelas' => null,
                'wali_murid_id' => $walis['wali2@pptq.test']->id,
                'tanggal_lahir' => '2008-01-05',
            ]
        );

        // 5. Penugasan Ustadz -> Kegiatan (Pivot)
        DB::table('ustadz_kegiatan')->updateOrInsert(
            ['user_id' => $asatidzah['ustadz1@pptq.test']->id, 'kegiatan_pondok_id' => $kegiatanPagi->id],
            ['created_at' => now()]
        );
        DB::table('ustadz_kegiatan')->updateOrInsert(
            ['user_id' => $asatidzah['ustadz4@pptq.test']->id, 'kegiatan_pondok_id' => $kegiatanPagi->id],
            ['created_at' => now()]
        );
        DB::table('ustadz_kegiatan')->updateOrInsert(
            ['user_id' => $asatidzah['ustadz2@pptq.test']->id, 'kegiatan_pondok_id' => $kegiatanIntensif->id],
            ['created_at' => now()]
        );
        DB::table('ustadz_kegiatan')->updateOrInsert(
            ['user_id' => $asatidzah['ustadz5@pptq.test']->id, 'kegiatan_pondok_id' => $kegiatanIntensif->id],
            ['created_at' => now()]
        );
        DB::table('ustadz_kegiatan')->updateOrInsert(
            ['user_id' => $asatidzah['ustadz3@pptq.test']->id, 'kegiatan_pondok_id' => $kegiatanFiqih->id],
            ['created_at' => now()]
        );

        // 6. Penugasan Ustadz -> Santri (Mentorship)
        DB::table('ustadz_santri')->updateOrInsert(
            ['user_id' => $asatidzah['ustadz1@pptq.test']->id, 'santri_id' => $santri1->id],
            ['created_at' => now()]
        );
        DB::table('ustadz_santri')->updateOrInsert(
            ['user_id' => $asatidzah['ustadz1@pptq.test']->id, 'santri_id' => $santri2->id],
            ['created_at' => now()]
        );
        DB::table('ustadz_santri')->updateOrInsert(
            ['user_id' => $asatidzah['ustadz2@pptq.test']->id, 'santri_id' => $santri3->id],
            ['created_at' => now()]
        );
        DB::table('ustadz_santri')->updateOrInsert(
            ['user_id' => $asatidzah['ustadz3@pptq.test']->id, 'santri_id' => $santri4->id],
            ['created_at' => now()]
        );

        // 7. Sesi Presensi & Riwayat
        $presensiSession = PresensiKegiatan::updateOrCreate(
            [
                'kegiatan_pondok_id' => $kegiatanPagi->id,
                'ustadz_id' => $asatidzah['ustadz1@pptq.test']->id,
                'tanggal' => Carbon::today()->startOfDay()->toDateTimeString(),
            ],
            [
                'hari' => 'Kamis',
                'jam_mulai' => '05:30:00',
                'jam_selesai' => '07:00:00',
                'materi' => 'Setoran Hafalan Surat An-Naba',
                'latitude' => -7.7970680,
                'longitude' => 110.3705290,
                'status' => 'valid',
            ]
        );

        PresensiSantri::updateOrCreate(
            [
                'presensi_kegiatan_id' => $presensiSession->id,
                'santri_id' => $santri1->id,
            ],
            [
                'status' => 'hadir',
            ]
        );

        PresensiSantri::updateOrCreate(
            [
                'presensi_kegiatan_id' => $presensiSession->id,
                'santri_id' => $santri2->id,
            ],
            [
                'status' => 'hadir',
            ]
        );

        // 8. Setoran Hafalan & Murojaah Sample
        SetoranHafalan::updateOrCreate(
            [
                'santri_id' => $santri1->id,
                'ustadz_id' => $asatidzah['ustadz1@pptq.test']->id,
                'tanggal' => Carbon::today()->startOfDay()->toDateTimeString(),
                'surat' => 'An-Naba',
            ],
            [
                'juz' => 30,
                'ayat' => '1-20',
                'nilai' => 92.50,
                'status_selesai' => true,
                'catatan' => 'Makhraj huruf shad dan sin sudah sangat baik',
            ]
        );

        Murojaah::updateOrCreate(
            [
                'santri_id' => $santri1->id,
                'ustadz_id' => $asatidzah['ustadz1@pptq.test']->id,
                'tanggal' => Carbon::today()->startOfDay()->toDateTimeString(),
                'surat' => 'Al-Mulk',
            ],
            [
                'juz' => 29,
                'ayat' => '1-30',
                'nilai' => 95.00,
                'status_selesai' => true,
            ]
        );
    }
}
