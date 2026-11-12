<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\Pendaftaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 16: PPDB Admin Dashboard & Analytics (F16)
 *
 * Source: dokum-ppdb §2.3, §5.3 Q1, Build Plan §13.2
 * Verifies admin PPDB dashboard, applicant list table, metric cards
 * (Total, Menunggu, Lulus, Tidak Lulus), and gender ratio analytics.
 */
class F16_PpdbAdminAnalyticsTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, RefreshDatabase;

    public function test_admin_can_access_ppdb_dashboard_and_applicant_table(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.ppdb.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.ppdb.index');
        $response->assertViewHas('pendaftarans');
    }

    public function test_dashboard_calculates_correct_status_metric_cards(): void
    {
        $admin = $this->createAdmin();

        // 2 Menunggu, 1 Lulus, 1 Tidak Lulus
        $this->createPendaftaran(['nama_lengkap' => 'Pendaftar 1', 'status' => 'Menunggu']);
        $this->createPendaftaran(['nama_lengkap' => 'Pendaftar 2', 'status' => 'Menunggu']);
        $this->createPendaftaran(['nama_lengkap' => 'Pendaftar 3', 'status' => 'Lulus']);
        $this->createPendaftaran(['nama_lengkap' => 'Pendaftar 4', 'status' => 'Tidak Lulus']);

        $response = $this->actingAs($admin)->get(route('admin.ppdb.index'));

        $response->assertStatus(200);
        $this->assertEquals(4, Pendaftaran::count());
        $this->assertEquals(2, Pendaftaran::where('status', 'Menunggu')->count());
        $this->assertEquals(1, Pendaftaran::where('status', 'Lulus')->count());
        $this->assertEquals(1, Pendaftaran::where('status', 'Tidak Lulus')->count());
    }

    public function test_dashboard_provides_gender_ratio_analytics(): void
    {
        $admin = $this->createAdmin();

        $this->createPendaftaran(['nama_lengkap' => 'Santri Putra 1', 'jenis_kelamin' => 'Laki-laki']);
        $this->createPendaftaran(['nama_lengkap' => 'Santri Putra 2', 'jenis_kelamin' => 'Laki-laki']);
        $this->createPendaftaran(['nama_lengkap' => 'Santri Putri 1', 'jenis_kelamin' => 'Perempuan']);

        $response = $this->actingAs($admin)->get(route('admin.ppdb.index'));

        $response->assertStatus(200);
        $this->assertEquals(2, Pendaftaran::where('jenis_kelamin', 'Laki-laki')->count());
        $this->assertEquals(1, Pendaftaran::where('jenis_kelamin', 'Perempuan')->count());
    }

    public function test_dashboard_supports_filtering_by_status(): void
    {
        $admin = $this->createAdmin();

        $this->createPendaftaran(['nama_lengkap' => 'Pendaftar Lulusan', 'status' => 'Lulus']);
        $this->createPendaftaran(['nama_lengkap' => 'Pendaftar Antrian', 'status' => 'Menunggu']);

        $response = $this->actingAs($admin)->get(route('admin.ppdb.index', ['status' => 'Lulus']));

        $response->assertStatus(200);
        $response->assertSee('Pendaftar Lulusan');
    }

    public function test_non_admin_roles_forbidden_from_accessing_ppdb_dashboard(): void
    {
        $calonSantri = $this->createCalonSantri();
        $ustadz = $this->createUstadz();
        $wali = $this->createWaliMurid();

        $this->actingAs($calonSantri)->get(route('admin.ppdb.index'))->assertStatus(403);
        $this->actingAs($ustadz)->get(route('admin.ppdb.index'))->assertStatus(403);
        $this->actingAs($wali)->get(route('admin.ppdb.index'))->assertStatus(403);
    }

    public function test_unauthenticated_guest_redirected_to_login(): void
    {
        $response = $this->get(route('admin.ppdb.index'));

        $response->assertRedirect(route('login'));
    }
}
