<?php

namespace Tests\Feature\Tier4_Scenarios;

use App\Models\KegiatanPondok;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\Support\MocksFonnteGateway;
use Tests\TestCase;

/**
 * Tier 4 Scenario Test: S02 Admin Pesantren Academic Cycle
 *
 * Source: dokum-cp1 §5.2, dokum-ppdb §5.3, Build Plan §16.2
 * Verifies complete administrative workflows across the academic cycle:
 * - Scenario 1: Academic Year Setup (Master Data CRUD, Penugasan Ustadz, Santri Binaan sync, Wali linking)
 * - Scenario 2: PPDB Intake Transition (Reviewing pendaftar, verification, promotion with 7-digit NIS,
 *               binaan assignment, and report generation).
 */
class S02_AdminPesantrenAcademicCycleTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, MocksFonnteGateway, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->mockFonnteSuccess();
    }

    /**
     * 1. Complete Academic Year Setup and Assignment Cycle.
     */
    public function test_admin_complete_academic_year_setup_and_assignment_cycle(): void
    {
        $admin = $this->createAdmin(['name' => 'Admin Utama Pesantren']);

        // Step 1: Admin views admin dashboard
        $dashboardResponse = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashboardResponse->assertStatus(200);

        // Step 2: Create Wali Murid account
        $waliResponse = $this->actingAs($admin)->post(route('admin.wali-murid.store'), [
            'name' => 'Bapak Joko Santoso',
            'email' => 'wali.joko_'.uniqid().'@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $waliResponse->assertRedirect();
        $wali = User::role('wali_murid')->latest('id')->firstOrFail();

        // Step 3: Create Santri with 7-digit NIS and link to Wali
        $santriResponse = $this->actingAs($admin)->post(route('admin.santri.store'), [
            'nama' => 'Muhammad Thariq Santoso',
            'nis' => '2026501',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7A',
            'wali_murid_id' => $wali->id,
            'tanggal_lahir' => '2012-03-25',
        ]);
        $santriResponse->assertRedirect();
        $santri = Santri::where('nis', '2026501')->firstOrFail();
        $this->assertEquals($wali->id, $santri->wali_murid_id);

        // Step 4: Create Ustadz account
        $ustadzResponse = $this->actingAs($admin)->post(route('admin.ustadz.store'), [
            'name' => 'Ustadz Hasan Al-Banna',
            'email' => 'ustadz.hasan_'.uniqid().'@ibnujuraimi.sch.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $ustadzResponse->assertRedirect();
        $ustadz = User::role('ustadz')->latest('id')->firstOrFail();

        // Step 5: Create Kegiatan Pondok
        $kegiatanResponse = $this->actingAs($admin)->post(route('admin.kegiatan.store'), [
            'nama_kegiatan' => 'Halaqah Tahfidz Pagi',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7A',
            'deskripsi' => 'Setoran hafalan baru rutin setelah sholat subuh.',
        ]);
        $kegiatanResponse->assertRedirect();
        $kegiatan = KegiatanPondok::where('nama_kegiatan', 'Halaqah Tahfidz Pagi')->firstOrFail();

        // Step 6: Assign Ustadz to Kegiatan Pondok
        $assignKegiatanResponse = $this->actingAs($admin)->put(
            route('admin.penugasan-kegiatan.update', $ustadz->id),
            ['kegiatan_ids' => [$kegiatan->id]]
        );
        $assignKegiatanResponse->assertRedirect();
        $this->assertTrue($ustadz->kegiatanPondok()->where('kegiatan_pondok.id', $kegiatan->id)->exists());

        // Step 7: Assign Santri Binaan to Ustadz
        $assignSantriResponse = $this->actingAs($admin)->put(
            route('admin.santri-binaan.update', $ustadz->id),
            ['santri_ids' => [$santri->id]]
        );
        $assignSantriResponse->assertRedirect();
        $this->assertTrue($ustadz->santriBinaan()->where('santri.id', $santri->id)->exists());

        // Step 8: Verify Detail Views
        $this->actingAs($admin)->get(route('admin.santri.show', $santri->id))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.ustadz.show', $ustadz->id))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.kegiatan.show', $kegiatan->id))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.wali-murid.show', $wali->id))->assertStatus(200);
    }

    /**
     * 2. PPDB Intake to Academic Promotion and Reporting Cycle.
     */
    public function test_admin_ppdb_intake_to_academic_promotion_and_reporting_cycle(): void
    {
        $admin = $this->createAdmin();
        $wali = $this->createWaliMurid(['name' => 'Wali Calon']);
        $ustadz = $this->createUstadz(['name' => 'Ustadz Pembina']);

        // Step 1: Admin views PPDB applicants table
        $pendaftar = $this->createPendaftaran([
            'nama_lengkap' => 'Calon Santri Unggulan',
            'email' => 'calon.unggulan_'.uniqid().'@example.com',
            'no_hp' => '081288776655',
            'status' => 'Menunggu',
            'santri_id' => null,
        ]);

        $ppdbListResponse = $this->actingAs($admin)->get(route('admin.ppdb.index'));
        $ppdbListResponse->assertStatus(200);
        $ppdbListResponse->assertSee('Calon Santri Unggulan');

        // Step 2: Admin verifies applicant as 'Lulus'
        $this->actingAs($admin)->post(route('admin.verifikasi', $pendaftar->id), [
            'status' => 'Lulus',
            'catatan' => 'Lulus seleksi berkas dan tes tahsin.',
        ])->assertSessionHasNoErrors();

        $this->assertFonnteSent('081288776655', 'Lulus');

        // Step 3: Admin promotes verified applicant to Santri with 7-digit NIS
        $promoteResponse = $this->actingAs($admin)->post(route('admin.pendaftar.promosikan', $pendaftar->id), [
            'nis' => '2026502',
            'tingkatan' => 'aliyah',
            'kelas' => '10 IPA',
            'wali_murid_id' => $wali->id,
        ]);
        $promoteResponse->assertRedirect();

        $newSantri = Santri::where('nis', '2026502')->firstOrFail();
        $this->assertEquals('Calon Santri Unggulan', $newSantri->nama);
        $this->assertEquals($wali->id, $newSantri->wali_murid_id);

        // Step 4: Admin assigns new santri to Ustadz Binaan
        $this->actingAs($admin)->put(route('admin.santri-binaan.update', $ustadz->id), [
            'santri_ids' => [$newSantri->id],
        ])->assertRedirect();
        $this->assertTrue($ustadz->santriBinaan()->where('santri.id', $newSantri->id)->exists());

        // Step 5: Admin exports PPDB reports (Excel & PDF)
        $excelResponse = $this->actingAs($admin)->get(route('admin.export.excel'));
        $excelResponse->assertStatus(200);

        $pdfResponse = $this->actingAs($admin)->get(route('admin.export.pdf'));
        $pdfResponse->assertStatus(200);
    }
}
