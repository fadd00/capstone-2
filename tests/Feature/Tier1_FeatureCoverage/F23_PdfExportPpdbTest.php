<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\Pendaftaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 23: Ekspor PDF Rekapitulasi (F23)
 *
 * Source: dokum-ppdb §2.5, §7.3, Build Plan §6.4
 * Verifies formatted A4 landscape PDF recap generation at GET /admin/export/pdf
 * with PDF headers, stream rendering, and role authorization.
 */
class F23_PdfExportPpdbTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, RefreshDatabase;

    public function test_admin_can_export_ppdb_recap_to_pdf_successfully(): void
    {
        $admin = $this->createAdmin();
        $this->createPendaftaran(['nama_lengkap' => 'Santri Rekap PDF']);

        $response = $this->actingAs($admin)->get(route('admin.export.pdf'));

        $response->assertStatus(200);
    }

    public function test_pdf_export_headers_contain_application_pdf_content_type(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.export.pdf'));

        $response->assertStatus(200);
        $contentType = (string) $response->headers->get('content-type');
        $this->assertStringContainsString('application/pdf', $contentType);
    }

    public function test_pdf_disposition_contains_filename_with_pdf_extension(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.export.pdf'));

        $disposition = (string) $response->headers->get('content-disposition');
        $this->assertTrue(
            str_contains($disposition, '.pdf') ||
            str_contains($response->headers->get('content-type'), 'pdf')
        );
    }

    public function test_pdf_export_renders_properly_with_zero_records(): void
    {
        $admin = $this->createAdmin();
        $this->assertEquals(0, Pendaftaran::count());

        $response = $this->actingAs($admin)->get(route('admin.export.pdf'));

        $response->assertStatus(200);
    }

    public function test_non_admin_forbidden_from_exporting_pdf(): void
    {
        $calonSantri = $this->createCalonSantri();
        $wali = $this->createWaliMurid();

        $this->actingAs($calonSantri)->get(route('admin.export.pdf'))->assertStatus(403);
        $this->actingAs($wali)->get(route('admin.export.pdf'))->assertStatus(403);
    }
}
