<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\KegiatanPondok;
use App\Models\Kontak;
use App\Models\Santri;
use App\Models\Statistik;
use App\Models\StatusForm;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class F05_SeedersTest extends TestCase
{
    /**
     * 1. DatabaseSeeder executes completely with exit code 0.
     */
    public function test_database_seeder_executes_successfully(): void
    {
        $exitCode = Artisan::call('db:seed');

        $this->assertSame(0, $exitCode, 'Artisan db:seed must execute cleanly with zero exit code.');
    }

    /**
     * 2. Seeders establish the 3 portal roles currently used by the application.
     */
    public function test_seeders_create_current_portal_roles(): void
    {
        Artisan::call('db:seed');

        $requiredRoles = ['admin', 'ustadz', 'wali_murid'];

        foreach ($requiredRoles as $roleName) {
            $this->assertTrue(
                Role::where('name', $roleName)->where('guard_name', 'web')->exists(),
                "Spatie role '{$roleName}' must be seeded."
            );
        }
    }

    /**
     * 3. Seeders establish at least one active administrator account assigned the 'admin' role.
     */
    public function test_seeders_create_default_admin_user_with_role(): void
    {
        Artisan::call('db:seed');

        $admin = User::role('admin')->first();

        $this->assertNotNull($admin, 'A default seeded user with role admin must exist.');
        $this->assertTrue($admin->hasRole('admin'));
    }

    /**
     * 4. Removed public PPDB/CMS features are not initialized by the application seeders.
     */
    public function test_seeders_do_not_initialize_removed_ppdb_cms_records(): void
    {
        Artisan::call('db:seed');

        $this->assertSame(0, StatusForm::count());
        $this->assertSame(0, Statistik::count());
        $this->assertSame(0, Kontak::count());
    }

    /**
     * 5. Seeders are idempotent: executing db:seed multiple times does not throw unique collision errors.
     */
    public function test_seeders_are_idempotent_when_executed_multiple_times(): void
    {
        $firstRun = Artisan::call('db:seed');
        $this->assertSame(0, $firstRun);

        $initialUserCount = User::count();
        $initialRoleCount = Role::count();

        // Second run must succeed idempotently without crashing
        $secondRun = Artisan::call('db:seed');
        $this->assertSame(0, $secondRun, 'Second consecutive db:seed execution must remain idempotent.');

        $this->assertSame($initialRoleCount, Role::count(), 'Role count must not duplicate on multiple seeder runs.');
    }

    /**
     * 6. Seeders populate sample academic master data for demo environments.
     */
    public function test_seeders_populate_academic_sample_data(): void
    {
        $exitCode = Artisan::call('db:seed');
        $this->assertSame(0, $exitCode, 'Artisan db:seed must execute cleanly.');

        $this->assertGreaterThanOrEqual(1, Santri::count(), 'Demo santri data should be populated.');
        $this->assertGreaterThanOrEqual(1, KegiatanPondok::count(), 'Demo kegiatan pondok data should be populated.');
    }
}
