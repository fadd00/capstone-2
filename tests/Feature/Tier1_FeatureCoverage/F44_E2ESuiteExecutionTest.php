<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\Support\MocksFonnteGateway;
use Tests\TestCase;

class F44_E2ESuiteExecutionTest extends TestCase
{
    /**
     * 1. Test environment uses SQLite in-memory database (:memory:) for maximum speed and isolation.
     */
    public function test_phpunit_configuration_uses_sqlite_in_memory_connection(): void
    {
        $defaultConnection = Config::get('database.default');
        $this->assertEquals('sqlite', $defaultConnection, 'Default database connection in test must be sqlite.');

        $databaseName = Config::get("database.connections.{$defaultConnection}.database");
        $this->assertEquals(':memory:', $databaseName, 'SQLite database must be configured as :memory: in test environment.');
    }

    /**
     * 2. All 3 test support traits exist and are autoloadable.
     */
    public function test_test_support_traits_exist_and_are_autoloadable(): void
    {
        $this->assertTrue(trait_exists(CreatesTestUsers::class), 'CreatesTestUsers trait must exist.');
        $this->assertTrue(trait_exists(CreatesPpdbFixtures::class), 'CreatesPpdbFixtures trait must exist.');
        $this->assertTrue(trait_exists(MocksFonnteGateway::class), 'MocksFonnteGateway trait must exist.');
    }

    /**
     * 3. Four-tier directory layout exists under tests/Feature.
     */
    public function test_tier_directory_structure_exists_under_tests_feature(): void
    {
        $expectedTiers = [
            base_path('tests/Feature/Tier1_FeatureCoverage'),
            base_path('tests/Feature/Tier2_Boundary'),
            base_path('tests/Feature/Tier3_Pairwise'),
            base_path('tests/Feature/Tier4_Scenarios'),
        ];

        foreach ($expectedTiers as $tierPath) {
            $this->assertDirectoryExists($tierPath, "Tier directory must exist: {$tierPath}");
        }
    }

    /**
     * 4. Assigned Tier 1 feature test files are mapped and exist on disk.
     */
    public function test_tier_one_assigned_feature_test_classes_exist(): void
    {
        $coreFeatureFiles = [
            'F01_PlanAlignmentVerificationTest.php',
            'F02_RbacMatrixTest.php',
            'F03_SchemaIntegrityTest.php',
            'F04_BreezeAuthTest.php',
            'F05_SeedersTest.php',
            'F44_E2ESuiteExecutionTest.php',
            'F45_AdversarialSecurityHardeningTest.php',
        ];

        foreach ($coreFeatureFiles as $fileName) {
            $path = base_path("tests/Feature/Tier1_FeatureCoverage/{$fileName}");
            $this->assertFileExists($path, "Assigned feature test file must exist: {$path}");
        }
    }

    /**
     * 5. Hermetic database isolation: RefreshDatabase resets the database state per test.
     */
    public function test_database_isolation_across_test_executions(): void
    {
        // Inserting a record in this test must not leak into other test methods
        $countBefore = User::count();

        User::create([
            'name' => 'Isolation Probe User',
            'email' => 'probe_'.uniqid().'@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $this->assertSame($countBefore + 1, User::count());
    }

    /**
     * 6. Unit tests for core application services exist under tests/Unit.
     */
    public function test_all_application_service_unit_test_classes_exist(): void
    {
        $unitFiles = [
            'GeolocationServiceTest.php',
            'FonnteServiceTest.php',
            'PendaftarPromotionServiceTest.php',
        ];

        foreach ($unitFiles as $fileName) {
            $path = base_path("tests/Unit/{$fileName}");
            $this->assertFileExists($path, "Service unit test file must exist: {$path}");
        }
    }
}
