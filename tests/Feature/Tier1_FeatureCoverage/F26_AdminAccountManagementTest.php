<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 26: Manajemen Akun Administrator (F26)
 *
 * Source: dokum-ppdb §5.3 Q9, Build Plan §6.4
 * Verifies admin account management, creation of admin users, password reset,
 * and critical self-deletion guard preventing an admin from deleting their own account.
 */
class F26_AdminAccountManagementTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    public function test_admin_can_view_list_of_administrators(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.akun.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.akun.index');
        $response->assertViewHas('admins');
    }

    public function test_admin_can_create_new_administrator_account(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.akun.store'), [
            'name' => 'Admin Baru',
            'email' => 'admin_baru@ibnujuraimi.sch.id',
            'password' => 'PasswordBaru123!',
            'password_confirmation' => 'PasswordBaru123!',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.akun.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Admin Baru',
            'email' => 'admin_baru@ibnujuraimi.sch.id',
        ]);

        $newAdmin = User::where('email', 'admin_baru@ibnujuraimi.sch.id')->firstOrFail();
        $this->assertTrue($newAdmin->hasRole('admin'));
        $this->assertTrue(Hash::check('PasswordBaru123!', $newAdmin->password));
    }

    public function test_admin_cannot_delete_their_own_account_self_deletion_guard(): void
    {
        $admin = $this->createAdmin(['email' => 'admin_self@ibnujuraimi.sch.id']);

        // Admin attempts to delete themselves
        $response = $this->actingAs($admin)->delete(route('admin.akun.destroy', $admin->id));

        // Must redirect with error session, not succeed deletion
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'email' => 'admin_self@ibnujuraimi.sch.id',
        ]);
    }

    public function test_admin_can_delete_another_administrator_account(): void
    {
        $adminPrimary = $this->createAdmin(['email' => 'admin_primary@ibnujuraimi.sch.id']);
        $adminSecondary = $this->createAdmin(['email' => 'admin_secondary@ibnujuraimi.sch.id']);

        $response = $this->actingAs($adminPrimary)->delete(route('admin.akun.destroy', $adminSecondary->id));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('users', [
            'id' => $adminSecondary->id,
        ]);
    }

    public function test_admin_can_reset_password_for_an_administrator(): void
    {
        $admin = $this->createAdmin();
        $targetAdmin = $this->createAdmin(['password' => Hash::make('OldPassword123')]);

        $response = $this->actingAs($admin)->post(route('admin.akun.reset.update', $targetAdmin->id), [
            'password' => 'NewSecurePassword456!',
            'password_confirmation' => 'NewSecurePassword456!',
        ]);

        $response->assertSessionHasNoErrors();

        $targetAdmin->refresh();
        $this->assertTrue(Hash::check('NewSecurePassword456!', $targetAdmin->password));
    }

    public function test_non_admin_forbidden_from_admin_account_management(): void
    {
        $calonSantri = $this->createCalonSantri();
        $targetAdmin = $this->createAdmin();

        $this->actingAs($calonSantri)->get(route('admin.akun.index'))->assertStatus(403);
        $this->actingAs($calonSantri)->post(route('admin.akun.store'), [
            'name' => 'Intruder',
            'email' => 'intruder@example.com',
            'password' => 'Password123',
        ])->assertStatus(403);
        $this->actingAs($calonSantri)->delete(route('admin.akun.destroy', $targetAdmin->id))->assertStatus(403);
    }
}
