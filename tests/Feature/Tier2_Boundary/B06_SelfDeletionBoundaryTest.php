<?php

namespace Tests\Feature\Tier2_Boundary;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Tier 2 Boundary Test: B06 Admin Self-Deletion Boundary
 *
 * Source: dokum-ppdb §5.3 Q9, Build Plan §6.4
 * Verifies boundaries around administrator account deletion:
 * - Admin cannot delete their own active account (self-deletion blocked)
 * - Admin can delete another admin when multiple admins exist
 * - System prevents deleting the last remaining admin account
 * - Non-admin users cannot access admin deletion endpoint (403 Forbidden)
 * - Deleting non-existent admin ID returns 404
 */
class B06_SelfDeletionBoundaryTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    /**
     * 1. Admin attempting to delete their own account is rejected and the account is retained.
     */
    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin1 = $this->createAdmin(['name' => 'Admin Satu']);
        $admin2 = $this->createAdmin(['name' => 'Admin Dua']);

        // Admin 1 attempts to delete Admin 1
        $response = $this->actingAs($admin1)->delete(route('admin.akun.destroy', $admin1->id));

        $this->assertTrue(
            $response->isRedirection() || $response->status() === 403,
            'Self-deletion must be blocked with redirection or forbidden status.'
        );

        $this->assertDatabaseHas('users', ['id' => $admin1->id]);
    }

    /**
     * 2. Admin can delete another admin account when multiple admins exist.
     */
    public function test_admin_can_delete_another_admin_account_when_multiple_exist(): void
    {
        $adminMain = $this->createAdmin(['name' => 'Super Admin']);
        $adminTarget = $this->createAdmin(['name' => 'Admin Secondary']);

        $response = $this->actingAs($adminMain)->delete(route('admin.akun.destroy', $adminTarget->id));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.akun.index'));

        $this->assertDatabaseMissing('users', ['id' => $adminTarget->id]);
        $this->assertDatabaseHas('users', ['id' => $adminMain->id]);
    }

    /**
     * 3. System prevents deleting the sole/last remaining admin in the system.
     */
    public function test_admin_cannot_delete_the_last_remaining_admin_in_system(): void
    {
        // Only one admin in the database
        $soleAdmin = $this->createAdmin(['name' => 'Sole Admin']);

        $response = $this->actingAs($soleAdmin)->delete(route('admin.akun.destroy', $soleAdmin->id));

        $this->assertTrue(
            $response->isRedirection() || $response->status() === 403,
            'Deleting the last remaining admin must be blocked.'
        );

        $this->assertDatabaseHas('users', ['id' => $soleAdmin->id]);
        $this->assertEquals(1, User::role('admin')->count());
    }

    /**
     * 4. Non-admin roles (ustadz, wali_murid, calon_santri) receive 403 Forbidden.
     */
    public function test_non_admin_roles_cannot_access_admin_deletion_endpoint(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz();
        $wali = $this->createWaliMurid();
        $calon = $this->createCalonSantri();

        $this->actingAs($ustadz)->delete(route('admin.akun.destroy', $admin->id))
            ->assertForbidden();

        $this->actingAs($wali)->delete(route('admin.akun.destroy', $admin->id))
            ->assertForbidden();

        $this->actingAs($calon)->delete(route('admin.akun.destroy', $admin->id))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    /**
     * 5. Deleting a non-existent admin ID returns 404 Not Found.
     */
    public function test_deleting_non_existent_admin_id_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->delete(route('admin.akun.destroy', 999999));

        $response->assertNotFound();
    }
}
