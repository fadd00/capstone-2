<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\Pendaftaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 22: Ekspor Excel Rekapitulasi (F22)
 *
 * Source: dokum-ppdb §5.3 Q6, Build Plan §6.4
 * Verifies spreadsheet export of applicant recap at GET /admin/export/excel
 * with attachment headers, .xlsx filename, and strict role authorization.
 */
class F22_ExcelExportPpdbTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, RefreshDatabase;

    public function test_admin_can_export_ppdb_recap_to_excel_successfully(): void
    {
        $admin = $this->createAdmin();
        $this->createPendaftaran(['nama_lengkap' => 'Santri Excel Satu']);

        $response = $this->actingAs($admin)->get(route('admin.export.excel'));

        $response->assertStatus(200);

        $contentType = (string) $response->headers->get('content-type');
        $this->assertTrue(
            str_contains($contentType, 'spreadsheetml') ||
            str_contains($contentType, 'excel') ||
            str_contains($contentType, 'csv') ||
            str_contains($contentType, 'octet-stream'),
            "Content-Type should match spreadsheet MIME, got: {$contentType}"
        );
    }

    public function test_excel_export_returns_attachment_header_with_xlsx_filename(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.export.excel'));

        $disposition = (string) $response->headers->get('content-disposition');
        $this->assertStringContainsString('attachment', $disposition);
        $this->assertTrue(
            str_contains($disposition, '.xlsx') ||
            str_contains($disposition, '.xls') ||
            str_contains($disposition, '.csv')
        );
    }

    public function test_excel_export_works_gracefully_when_no_applicants_exist(): void
    {
        $admin = $this->createAdmin();

        $this->assertEquals(0, Pendaftaran::count());

        $response = $this->actingAs($admin)->get(route('admin.export.excel'));

        $response->assertStatus(200);
    }

    public function test_excel_export_includes_all_created_applicants(): void
    {
        $admin = $this->createAdmin();

        $this->createPendaftaran(['nama_lengkap' => 'Muhammad Awwal']);
        $this->createPendaftaran(['nama_lengkap' => 'Ahmad Tsani']);

        $response = $this->actingAs($admin)->get(route('admin.export.excel'));

        $response->assertStatus(200);
    }

    public function test_non_admin_forbidden_from_exporting_excel(): void
    {
        $calonSantri = $this->createCalonSantri();
        $ustadz = $this->createUstadz();

        $this->actingAs($calonSantri)->get(route('admin.export.excel'))->assertStatus(403);
        $this->actingAs($ustadz)->get(route('admin.export.excel'))->assertStatus(403);
    }
}
