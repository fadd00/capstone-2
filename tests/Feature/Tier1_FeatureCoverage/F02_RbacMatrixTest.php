<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use Spatie\Permission\Models\Role;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

class F02_RbacMatrixTest extends TestCase
{
    use CreatesTestUsers;

    /**
     * 1. All 4 Spatie roles (admin, ustadz, wali_murid, calon_santri) can be created and assigned.
     */
    public function test_four_spatie_roles_can_be_assigned_and_inspected(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz();
        $wali = $this->createWaliMurid();
        $calon = $this->createCalonSantri();

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($ustadz->hasRole('ustadz'));
        $this->assertTrue($wali->hasRole('wali_murid'));
        $this->assertTrue($calon->hasRole('calon_santri'));

        $this->assertFalse($ustadz->hasRole('admin'));
        $this->assertFalse($wali->hasRole('ustadz'));
        $this->assertFalse($calon->hasRole('wali_murid'));
    }

    /**
     * 2. Admin role has access to administrative dashboard.
     */
    public function test_admin_role_can_access_admin_dashboard(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $this->assertContains($response->getStatusCode(), [200, 302]);
        $this->assertNotEquals(403, $response->getStatusCode(), 'Admin must not receive 403 on admin dashboard.');
    }

    /**
     * 3. Non-admin roles receive 403 Forbidden when attempting to access admin routes.
     */
    public function test_non_admin_roles_receive_forbidden_on_admin_routes(): void
    {
        $ustadz = $this->createUstadz();
        $wali = $this->createWaliMurid();
        $calon = $this->createCalonSantri();

        $this->actingAs($ustadz)->get('/admin/dashboard')->assertStatus(403);
        $this->actingAs($wali)->get('/admin/dashboard')->assertStatus(403);
        $this->actingAs($calon)->get('/admin/dashboard')->assertStatus(403);
    }

    /**
     * 4. Ustadz role can access ustadz portal while other non-ustadz roles are forbidden.
     */
    public function test_ustadz_role_can_access_ustadz_routes_and_others_are_forbidden(): void
    {
        $ustadz = $this->createUstadz();
        $wali = $this->createWaliMurid();
        $calon = $this->createCalonSantri();

        $response = $this->actingAs($ustadz)->get('/ustadz/dashboard');
        $this->assertContains($response->getStatusCode(), [200, 302]);
        $this->assertNotEquals(403, $response->getStatusCode());

        $this->actingAs($wali)->get('/ustadz/dashboard')->assertStatus(403);
        $this->actingAs($calon)->get('/ustadz/dashboard')->assertStatus(403);
    }

    /**
     * 5. Wali Murid can access wali portal and non-wali are forbidden.
     */
    public function test_wali_murid_can_access_wali_portal_and_others_are_forbidden(): void
    {
        $wali = $this->createWaliMurid();
        $ustadz = $this->createUstadz();
        $calon = $this->createCalonSantri();

        $response = $this->actingAs($wali)->get('/wali-murid/dashboard');
        $this->assertContains($response->getStatusCode(), [200, 302]);
        $this->assertNotEquals(403, $response->getStatusCode());

        $this->actingAs($ustadz)->get('/wali-murid/dashboard')->assertStatus(403);
        $this->actingAs($calon)->get('/wali-murid/dashboard')->assertStatus(403);
    }

    /**
     * 6. Calon Santri can access applicant dashboard and others are forbidden.
     */
    public function test_calon_santri_can_access_applicant_dashboard_and_others_are_forbidden(): void
    {
        $calon = $this->createCalonSantri();
        $ustadz = $this->createUstadz();
        $wali = $this->createWaliMurid();

        $response = $this->actingAs($calon)->get('/user/dashboard');
        $this->assertContains($response->getStatusCode(), [200, 302]);
        $this->assertNotEquals(403, $response->getStatusCode());

        $this->actingAs($ustadz)->get('/user/dashboard')->assertStatus(403);
        $this->actingAs($wali)->get('/user/dashboard')->assertStatus(403);
    }

    /**
     * 7. Unauthenticated guests are redirected to login for all protected role endpoints.
     */
    public function test_unauthenticated_guests_are_redirected_to_login_for_protected_portals(): void
    {
        $protectedRoutes = [
            '/admin/dashboard',
            '/ustadz/dashboard',
            '/wali-murid/dashboard',
            '/user/dashboard',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertStatus(302);
            $response->assertRedirect('/login');
        }
    }
}
