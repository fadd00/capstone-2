<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DemoPesantrenSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RbacMatrixTest extends TestCase
{
    use RefreshDatabase;

    public function test_three_canonical_roles_created_on_web_guard(): void
    {
        $this->seed(RoleSeeder::class);

        $expectedRoles = ['admin', 'ustadz', 'wali_murid'];

        foreach ($expectedRoles as $roleName) {
            $this->assertDatabaseHas('roles', [
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role = Role::findByName($roleName, 'web');
            $this->assertNotNull($role);
            $this->assertEquals($roleName, $role->name);
        }

        $this->assertEquals(3, Role::count());
    }

    public function test_users_can_be_assigned_and_checked_for_roles(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::create([
            'name' => 'Admin Role User',
            'email' => 'admin_role@test.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertFalse($admin->hasRole('ustadz'));
        $this->assertFalse($admin->hasRole('wali_murid'));

        $ustadz = User::create([
            'name' => 'Ustadz Role User',
            'email' => 'ustadz_role@test.com',
            'password' => Hash::make('password'),
        ]);
        $ustadz->assignRole('ustadz');
        $this->assertTrue($ustadz->hasRole('ustadz'));
        $this->assertFalse($ustadz->hasRole('admin'));

        $wali = User::create([
            'name' => 'Wali Role User',
            'email' => 'wali_role@test.com',
            'password' => Hash::make('password'),
        ]);
        $wali->assignRole('wali_murid');
        $this->assertTrue($wali->hasRole('wali_murid'));
        $this->assertFalse($wali->hasRole('admin'));

    }

    public function test_seeders_correctly_assign_roles_to_seeded_users(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(AdminUserSeeder::class);
        $this->seed(DemoPesantrenSeeder::class);

        // Check Admins
        $adminPpdb = User::where('email', 'Muhammadiyah@gmail.com')->first();
        $this->assertNotNull($adminPpdb);
        $this->assertTrue($adminPpdb->hasRole('admin'));

        $adminPesantren = User::where('email', 'admin@ibnujuraimi.sch.id')->first();
        $this->assertNotNull($adminPesantren);
        $this->assertTrue($adminPesantren->hasRole('admin'));

        $adminTest = User::where('email', 'admin@pptq.test')->first();
        $this->assertNotNull($adminTest);
        $this->assertTrue($adminTest->hasRole('admin'));

        // Check Ustadz
        for ($i = 1; $i <= 5; $i++) {
            $ustadz = User::where('email', "ustadz{$i}@pptq.test")->first();
            $this->assertNotNull($ustadz);
            $this->assertTrue($ustadz->hasRole('ustadz'));
        }

        // Check Wali Murid
        for ($i = 1; $i <= 2; $i++) {
            $wali = User::where('email', "wali{$i}@pptq.test")->first();
            $this->assertNotNull($wali);
            $this->assertTrue($wali->hasRole('wali_murid'));
        }
    }

    public function test_route_middleware_role_alias_resolves_correctly_for_admin_and_non_admin_users(): void
    {
        $this->seed(RoleSeeder::class);

        Route::get('/test-role-middleware-alias', function () {
            return response()->json(['message' => 'admin-access-granted']);
        })->middleware(['auth', 'role:admin']);

        $admin = User::create([
            'name' => 'Admin Test Route User',
            'email' => 'admin_route_test@test.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        $ustadz = User::create([
            'name' => 'Ustadz Test Route User',
            'email' => 'ustadz_route_test@test.com',
            'password' => Hash::make('password'),
        ]);
        $ustadz->assignRole('ustadz');

        $wali = User::create([
            'name' => 'Wali Test Route User',
            'email' => 'wali_route_test@test.com',
            'password' => Hash::make('password'),
        ]);
        $wali->assignRole('wali_murid');

        // Unauthenticated -> redirected to login
        $this->get('/test-role-middleware-alias')
            ->assertRedirect('/login');

        // Admin -> 200 OK
        $this->actingAs($admin)
            ->get('/test-role-middleware-alias')
            ->assertStatus(200)
            ->assertJson(['message' => 'admin-access-granted']);

        // Non-admin (Ustadz) -> 403 Forbidden
        $this->actingAs($ustadz)
            ->get('/test-role-middleware-alias')
            ->assertStatus(403);

        // Non-admin (Wali Murid) -> 403 Forbidden
        $this->actingAs($wali)
            ->get('/test-role-middleware-alias')
            ->assertStatus(403);

        // Test role_or_permission alias resolution
        Route::get('/test-role-or-perm-alias', function () {
            return response()->json(['message' => 'role-or-permission-granted']);
        })->middleware(['auth', 'role_or_permission:admin|ustadz']);

        $this->actingAs($ustadz)
            ->get('/test-role-or-perm-alias')
            ->assertStatus(200);

        $this->actingAs($wali)
            ->get('/test-role-or-perm-alias')
            ->assertStatus(403);
    }
}
