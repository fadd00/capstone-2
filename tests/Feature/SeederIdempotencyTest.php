<?php

namespace Tests\Feature;

use App\Models\Carousel;
use App\Models\Informasi;
use App\Models\KegiatanPondok;
use App\Models\Keunggulan;
use App\Models\Kontak;
use App\Models\Murojaah;
use App\Models\PresensiKegiatan;
use App\Models\PresensiSantri;
use App\Models\Santri;
use App\Models\SetoranHafalan;
use App\Models\Statistik;
use App\Models\StatusForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeederIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_are_fully_idempotent_and_leave_table_counts_unchanged(): void
    {
        // First execution of all seeders
        $firstExitCode = Artisan::call('db:seed');
        $this->assertSame(0, $firstExitCode, 'First execution of db:seed must succeed with exit code 0.');

        $baselineCounts = [
            'roles' => Role::count(),
            'users' => User::count(),
            'santri' => Santri::count(),
            'kegiatan_pondok' => KegiatanPondok::count(),
            'ustadz_kegiatan' => DB::table('ustadz_kegiatan')->count(),
            'ustadz_santri' => DB::table('ustadz_santri')->count(),
            'presensi_kegiatan' => PresensiKegiatan::count(),
            'presensi_santri' => PresensiSantri::count(),
            'setoran_hafalan' => SetoranHafalan::count(),
            'murojaah' => Murojaah::count(),
            'status_forms' => StatusForm::count(),
            'statistiks' => Statistik::count(),
            'kontaks' => Kontak::count(),
            'carousels' => Carousel::count(),
            'keunggulans' => Keunggulan::count(),
            'informasis' => Informasi::count(),
        ];

        // Sanity check baseline counts
        $this->assertSame(4, $baselineCounts['roles']);
        $this->assertSame(10, $baselineCounts['users']);
        $this->assertSame(4, $baselineCounts['santri']);
        $this->assertSame(4, $baselineCounts['kegiatan_pondok']);
        $this->assertSame(1, $baselineCounts['presensi_kegiatan']);
        $this->assertSame(2, $baselineCounts['presensi_santri']);
        $this->assertSame(1, $baselineCounts['setoran_hafalan']);
        $this->assertSame(1, $baselineCounts['murojaah']);

        // Second consecutive execution of all seeders without fresh migration
        $secondExitCode = Artisan::call('db:seed');
        $this->assertSame(0, $secondExitCode, 'Second consecutive execution of db:seed must succeed with exit code 0.');

        $afterSecondRunCounts = [
            'roles' => Role::count(),
            'users' => User::count(),
            'santri' => Santri::count(),
            'kegiatan_pondok' => KegiatanPondok::count(),
            'ustadz_kegiatan' => DB::table('ustadz_kegiatan')->count(),
            'ustadz_santri' => DB::table('ustadz_santri')->count(),
            'presensi_kegiatan' => PresensiKegiatan::count(),
            'presensi_santri' => PresensiSantri::count(),
            'setoran_hafalan' => SetoranHafalan::count(),
            'murojaah' => Murojaah::count(),
            'status_forms' => StatusForm::count(),
            'statistiks' => Statistik::count(),
            'kontaks' => Kontak::count(),
            'carousels' => Carousel::count(),
            'keunggulans' => Keunggulan::count(),
            'informasis' => Informasi::count(),
        ];

        $this->assertSame($baselineCounts, $afterSecondRunCounts, 'Table counts must remain identical after second db:seed run.');

        // Third consecutive execution to guarantee multi-run idempotency
        $thirdExitCode = Artisan::call('db:seed');
        $this->assertSame(0, $thirdExitCode, 'Third consecutive execution of db:seed must succeed with exit code 0.');

        $afterThirdRunCounts = [
            'roles' => Role::count(),
            'users' => User::count(),
            'santri' => Santri::count(),
            'kegiatan_pondok' => KegiatanPondok::count(),
            'ustadz_kegiatan' => DB::table('ustadz_kegiatan')->count(),
            'ustadz_santri' => DB::table('ustadz_santri')->count(),
            'presensi_kegiatan' => PresensiKegiatan::count(),
            'presensi_santri' => PresensiSantri::count(),
            'setoran_hafalan' => SetoranHafalan::count(),
            'murojaah' => Murojaah::count(),
            'status_forms' => StatusForm::count(),
            'statistiks' => Statistik::count(),
            'kontaks' => Kontak::count(),
            'carousels' => Carousel::count(),
            'keunggulans' => Keunggulan::count(),
            'informasis' => Informasi::count(),
        ];

        $this->assertSame($baselineCounts, $afterThirdRunCounts, 'Table counts must remain identical after third db:seed run.');
    }
}
