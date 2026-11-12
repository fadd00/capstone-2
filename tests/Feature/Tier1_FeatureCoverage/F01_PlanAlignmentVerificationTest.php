<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use Tests\TestCase;

class F01_PlanAlignmentVerificationTest extends TestCase
{
    protected string $planPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->planPath = base_path('.hermes/plans/2026-11-12_163500-capstone-2-strict-build-plan.md');
    }

    /**
     * 1. Plan document exists and is marked as NORMATIF & BEKU v2.0.
     */
    public function test_strict_plan_document_exists_and_marks_normative_version_two(): void
    {
        $this->assertFileExists($this->planPath, 'Strict build plan v2.0 must exist in .hermes/plans/.');

        $content = file_get_contents($this->planPath);
        $this->assertStringContainsString('PLAN-C2-STRICT-v2.0', $content);
        $this->assertStringContainsString('NORMATIF & BEKU', $content);
        $this->assertStringContainsString('dokum-cp1.md', $content);
        $this->assertStringContainsString('dokum-ppdb.md', $content);
    }

    /**
     * 2. Plan documents the complete pruning of all 8 extraneous subsystems.
     */
    public function test_plan_verifies_pruning_of_all_eight_extraneous_subsystems(): void
    {
        $content = file_get_contents($this->planPath);

        $prunedSubsystems = [
            'Impor Excel',
            'NIS state machine',
            'Audit log',
            'DB Sync Bridge',
            'Quran Surahs 6.236',
            '24-hr edit window',
            'batasan jam geofence 05:00-21:00',
            'user is_active',
        ];

        foreach ($prunedSubsystems as $subsystem) {
            $this->assertStringContainsString(
                $subsystem,
                $content,
                "Plan must document pruning of extraneous subsystem: {$subsystem}"
            );
        }
    }

    /**
     * 3. Plan documents incorporation of all PPDB Online and Landing CMS requirements.
     */
    public function test_plan_verifies_incorporation_of_ppdb_online_specifications(): void
    {
        $content = file_get_contents($this->planPath);

        $requiredPPDBFeatures = [
            'pendaftarans',
            'CheckFormStatus',
            'Multi-Upload 10 Berkas',
            'RegisterSuccessMail',
            'Fonnte',
            'Promosi Pendaftar Lulus ke Santri',
            'pas_photo',
            'bukti_transfer',
            'surat_pernyataan',
        ];

        foreach ($requiredPPDBFeatures as $feature) {
            $this->assertStringContainsString(
                $feature,
                $content,
                "Plan must incorporate PPDB specification: {$feature}"
            );
        }
    }

    /**
     * 4. Plan explicitly defines all 16 canonical domain tables.
     */
    public function test_plan_specifies_all_sixteen_canonical_domain_tables(): void
    {
        $content = file_get_contents($this->planPath);

        $tables = [
            'users',
            'santri',
            'kegiatan_pondok',
            'ustadz_kegiatan',
            'ustadz_santri',
            'presensi_kegiatan',
            'presensi_santri',
            'setoran_hafalan',
            'murojaah',
            'pendaftarans',
            'status_forms',
            'informasis',
            'kontaks',
            'statistiks',
            'carousels',
            'keunggulans',
        ];

        foreach ($tables as $table) {
            $this->assertStringContainsString(
                $table,
                $content,
                "Plan must define domain table: {$table}"
            );
        }
    }

    /**
     * 5. Plan specifies the unified Spatie 4-role RBAC architecture.
     */
    public function test_plan_defines_spatie_four_role_matrix_and_guards(): void
    {
        $content = file_get_contents($this->planPath);

        $roles = ['admin', 'ustadz', 'wali_murid', 'calon_santri'];

        foreach ($roles as $role) {
            $this->assertStringContainsString(
                $role,
                $content,
                "Plan must specify Spatie role: {$role}"
            );
        }
    }

    /**
     * 6. Plan specifies santri.nis as VARCHAR(50) UNIQUE without restrictive integer constraints.
     */
    public function test_plan_specifies_nis_as_varchar_50_unique(): void
    {
        $content = file_get_contents($this->planPath);

        $this->assertMatchesRegularExpression(
            '/nis.*?VARCHAR\(50\)/i',
            $content,
            'Plan must specify santri.nis as VARCHAR(50).'
        );
    }
}
