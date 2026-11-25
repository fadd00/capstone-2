<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 14: Dashboard Calon Santri (F14)
 *
 * Source: dokum-ppdb §5.2, §7.2, Build Plan §7.5
 * Verifies applicant status tracker on /user/dashboard
 * with status indicators (Menunggu, Lulus, Tidak Lulus) and Spatie RBAC.
 */
#[\PHPUnit\Framework\Attributes\Group('retired-ppdb')]
class F14_CalonSantriDashboardTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, RefreshDatabase;

    public function test_calon_santri_can_access_dashboard_and_see_registration_record(): void
    {
        $calonSantri = $this->createCalonSantri(['name' => 'Ahmad Fathi']);
        $pendaftaran = $this->createPendaftaran([
            'user_id' => $calonSantri->id,
            'nama_lengkap' => 'Ahmad Fathi',
            'status' => 'Menunggu',
        ], $calonSantri);

        $response = $this->actingAs($calonSantri)->get(route('user.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('user.dashboard');
        $response->assertSee('Ahmad Fathi');
    }

    public function test_dashboard_displays_menunggu_status_badge(): void
    {
        $calonSantri = $this->createCalonSantri();
        $this->createPendaftaran([
            'user_id' => $calonSantri->id,
            'status' => 'Menunggu',
        ], $calonSantri);

        $response = $this->actingAs($calonSantri)->get(route('user.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Menunggu');
    }

    public function test_dashboard_displays_lulus_status_badge(): void
    {
        $calonSantri = $this->createCalonSantri();
        $this->createPendaftaran([
            'user_id' => $calonSantri->id,
            'status' => 'Lulus',
        ], $calonSantri);

        $response = $this->actingAs($calonSantri)->get(route('user.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Lulus');
    }

    public function test_dashboard_displays_tidak_lulus_status_badge(): void
    {
        $calonSantri = $this->createCalonSantri();
        $this->createPendaftaran([
            'user_id' => $calonSantri->id,
            'status' => 'Tidak Lulus',
        ], $calonSantri);

        $response = $this->actingAs($calonSantri)->get(route('user.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Tidak Lulus');
    }

    public function test_guest_redirected_to_login_when_accessing_applicant_dashboard(): void
    {
        $response = $this->get(route('user.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_other_roles_cannot_access_calon_santri_dashboard(): void
    {
        $ustadz = $this->createUstadz();
        $wali = $this->createWaliMurid();

        $responseUstadz = $this->actingAs($ustadz)->get(route('user.dashboard'));
        $this->assertTrue(in_array($responseUstadz->getStatusCode(), [403, 302]));

        $responseWali = $this->actingAs($wali)->get(route('user.dashboard'));
        $this->assertTrue(in_array($responseWali->getStatusCode(), [403, 302]));
    }
}
