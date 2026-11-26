<?php

namespace Tests\Feature;

use App\Models\KegiatanPondok;
use App\Models\Murojaah;
use App\Models\PresensiKegiatan;
use App\Models\PresensiSantri;
use App\Models\Santri;
use App\Models\SetoranHafalan;
use Database\Seeders\RoleSeeder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

class DataIntegrityHardeningTest extends TestCase
{
    use CreatesTestUsers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class); // seperti di aplikasi: role selalu ada
    }

    private function sesi(string $status = 'valid'): array
    {
        $ustadz = $this->createUstadz();
        $kegiatan = KegiatanPondok::create(['nama_kegiatan' => 'Tahfidz 7', 'tingkatan' => 'tsanawiyah', 'kelas' => '7']);
        $peserta = Santri::create(['nama' => 'Peserta', 'nis' => 'P1', 'tingkatan' => 'tsanawiyah', 'kelas' => '7']);
        $luar = Santri::create(['nama' => 'Luar Kelas', 'nis' => 'L1', 'tingkatan' => 'aliyah', 'kelas' => '10']);
        $sesi = PresensiKegiatan::create([
            'kegiatan_pondok_id' => $kegiatan->id, 'ustadz_id' => $ustadz->id, 'tanggal' => '2026-11-26',
            'hari' => 'Kamis', 'jam_mulai' => '05:30:00', 'status' => $status,
        ]);

        return [$ustadz, $kegiatan, $peserta, $luar, $sesi];
    }

    public function test_kegiatan_and_santri_with_history_cannot_be_deleted(): void
    {
        $admin = $this->createAdmin();
        [, $kegiatan, $peserta, , $sesi] = $this->sesi();
        PresensiSantri::create(['presensi_kegiatan_id' => $sesi->id, 'santri_id' => $peserta->id, 'status' => 'hadir']);

        $this->actingAs($admin)->delete(route('admin.kegiatan.destroy', $kegiatan->id))->assertSessionHas('error');
        $this->actingAs($admin)->delete(route('admin.santri.destroy', $peserta->id))->assertSessionHas('error');

        $this->assertNotNull($kegiatan->fresh());
        $this->assertNotNull($peserta->fresh());
        $this->assertDatabaseCount('presensi_santri', 1);

        // Tanpa riwayat tetap bisa dihapus.
        $baru = Santri::create(['nama' => 'Baru', 'nis' => 'B1', 'tingkatan' => 'aliyah']);
        $this->actingAs($admin)->delete(route('admin.santri.destroy', $baru->id))->assertSessionHas('success');
        $this->assertNull($baru->fresh());
    }

    public function test_presensi_santri_only_accepts_peserta_and_valid_sessions(): void
    {
        [$ustadz, , $peserta, $luar, $sesi] = $this->sesi();

        $this->actingAs($ustadz)->get(route('ustadz.presensi-santri.create', $sesi->id))
            ->assertOk()->assertSee('Peserta')->assertDontSee('Luar Kelas');

        $this->actingAs($ustadz)->post(route('ustadz.presensi-santri.store', $sesi->id), [
            'presensi' => [$peserta->id => 'hadir', $luar->id => 'hadir'],
        ])->assertSessionHasErrors('presensi');
        $this->assertDatabaseCount('presensi_santri', 0);

        $this->actingAs($ustadz)->post(route('ustadz.presensi-santri.store', $sesi->id), [
            'presensi' => [$peserta->id => 'hadir'],
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('presensi_santri', 1);

        $sesi->update(['status' => 'invalid']);
        $this->actingAs($ustadz)->post(route('ustadz.presensi-santri.store', $sesi->id), [
            'presensi' => [$peserta->id => 'izin'],
        ])->assertSessionHasErrors('presensi');
        $this->assertDatabaseHas('presensi_santri', ['santri_id' => $peserta->id, 'status' => 'hadir']);
    }

    public function test_murojaah_catatan_is_saved(): void
    {
        [$ustadz, , $peserta] = $this->sesi();
        $ustadz->santriBinaan()->attach($peserta->id);

        $this->actingAs($ustadz)->post(route('ustadz.murojaah.store'), [
            'santri_id' => $peserta->id, 'tanggal' => '2026-11-26', 'surat' => 'Al-Mulk',
            'ayat_mulai' => 1, 'ayat_selesai' => 30, 'catatan' => 'Perhatikan mad wajib',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Perhatikan mad wajib', Murojaah::first()->catatan);
    }

    public function test_malformed_filters_and_ids_do_not_cause_server_errors(): void
    {
        $admin = $this->createAdmin();
        $wali = $this->createWaliMurid();

        $this->actingAs($wali)->get('/wali-murid/laporan-mingguan?tanggal_mulai=abc')->assertRedirect();
        $this->actingAs($wali)->get('/wali-murid/riwayat-kegiatan?bulan[]=x')->assertRedirect();
        $this->actingAs($admin)->get('/admin/laporan/hafalan?bulan[]=x')->assertRedirect();
        $this->actingAs($admin)->get('/admin/laporan/export?laporan=../x&format=excel')->assertRedirect();
        $this->actingAs($admin)->get('/admin/santri?search[]=x')->assertRedirect();
        $this->actingAs($admin)->get('/admin/kegiatan/abc')->assertNotFound();
        $this->actingAs($admin)->get('/admin/laporan/hafalan?bulan=2026-11')->assertOk();
    }

    public function test_assignments_respect_roles(): void
    {
        $admin = $this->createAdmin();
        $wali = $this->createWaliMurid();
        $kegiatan = KegiatanPondok::create(['nama_kegiatan' => 'K', 'tingkatan' => 'semua']);
        $santri = Santri::create(['nama' => 'S', 'nis' => 'S1', 'tingkatan' => 'aliyah']);

        $this->actingAs($admin)->put(route('admin.penugasan-kegiatan.update', $wali->id), ['kegiatan_ids' => [$kegiatan->id]])
            ->assertNotFound();
        $this->assertSame(0, $wali->kegiatanPondok()->count());

        $this->actingAs($admin)->put(route('admin.santri.update', $santri->id), [
            'nama' => 'S', 'nis' => 'S1', 'tingkatan' => 'aliyah', 'wali_murid_id' => $admin->id,
        ])->assertSessionHasErrors('wali_murid_id');

        $this->actingAs($admin)->put(route('admin.santri.update', $santri->id), [
            'nama' => 'S', 'nis' => 'S1', 'tingkatan' => 'aliyah', 'wali_murid_id' => $wali->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame($wali->id, $santri->fresh()->wali_murid_id);
    }

    public function test_excel_export_stores_formula_like_text_as_plain_text(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz();
        $santri = Santri::create(['nama' => '=HYPERLINK("http://contoh")', 'nis' => 'X1', 'tingkatan' => 'aliyah']);
        SetoranHafalan::create([
            'santri_id' => $santri->id, 'ustadz_id' => $ustadz->id, 'tanggal' => '2026-11-26',
            'juz' => 30, 'surat' => 'An-Naba', 'ayat' => '1-5',
        ]);

        $response = $this->actingAs($admin)->get('/admin/laporan/export?laporan=hafalan&format=excel')->assertOk();

        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $response->getContent());
        $cell = IOFactory::load($path)->getActiveSheet()->getCell('B2');
        unlink($path);

        $this->assertSame('s', $cell->getDataType());
        $this->assertSame('=HYPERLINK("http://contoh")', $cell->getValue());
    }
}
