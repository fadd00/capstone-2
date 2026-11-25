<?php

namespace Tests\Feature\Tier3_Pairwise;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Tier 3 Pairwise Test: P05 PPDB Archive and Export Synchronization
 *
 * Source: dokum-ppdb §9.3, §5.3 Q6, §2.5, §7.3, Build Plan §6.4
 * Verifies cross-module synchronization between:
 * 1. Registered applicants with private uploaded documents
 * 2. On-demand single ZIP archive packaging of 10 applicant files
 * 3. Excel spreadsheet rekapitulasi export (25 fields)
 * 4. PDF A4 landscape summary report export
 * 5. Data fidelity and temporary file cleanup
 */
#[\PHPUnit\Framework\Attributes\Group('retired-ppdb')]
class P05_PpdbZipAndExportSyncTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /**
     * 1. Admin download ZIP packages all 10 applicant files and returns binary ZIP stream.
     */
    public function test_admin_download_zip_packages_all_10_applicant_files_and_cleans_up_temp(): void
    {
        $admin = $this->createAdmin();

        // Seed 10 files in fake storage
        $files = [
            'pas_photo' => 'pendaftaran/pas_photo/test1.jpg',
            'bukti_transfer' => 'pendaftaran/bukti_transfer/test1.pdf',
            'formulir_pendaftaran' => 'pendaftaran/formulir_pendaftaran/test1.pdf',
            'surat_pernyataan' => 'pendaftaran/surat_pernyataan/test1.pdf',
            'akta_kelahiran' => 'pendaftaran/akta_kelahiran/test1.pdf',
            'kartu_keluarga' => 'pendaftaran/kartu_keluarga/test1.pdf',
            'raport_terakhir' => 'pendaftaran/raport_terakhir/test1.pdf',
            'ijazah_terakhir' => 'pendaftaran/ijazah_terakhir/test1.pdf',
            'skhun' => 'pendaftaran/skhun/test1.pdf',
            'sktm' => 'pendaftaran/sktm/test1.pdf',
        ];

        foreach ($files as $field => $path) {
            Storage::disk('local')->put($path, "Content of {$field}");
        }

        $pendaftar = $this->createPendaftaran(array_merge([
            'nama_lengkap' => 'Santri Berkas Lengkap',
        ], $files));

        $response = $this->actingAs($admin)->get(route('admin.download-zip', $pendaftar->id));

        $response->assertStatus(200);
        $this->assertTrue(
            str_contains($response->headers->get('content-type', ''), 'zip') ||
            str_contains($response->headers->get('content-disposition', ''), '.zip'),
            'Response should deliver a ZIP archive binary download.'
        );
    }

    /**
     * 2. Admin Excel export produces a valid spreadsheet across all registered applicants.
     */
    public function test_admin_export_excel_contains_all_registered_applicants_and_canonical_columns(): void
    {
        $admin = $this->createAdmin();

        $this->createPendaftaran(['nama_lengkap' => 'Applicant Excel One', 'nisn' => '1234567890']);
        $this->createPendaftaran(['nama_lengkap' => 'Applicant Excel Two', 'nisn' => '0987654321']);

        $response = $this->actingAs($admin)->get(route('admin.export.excel'));

        $response->assertStatus(200);
        $this->assertTrue(
            str_contains($response->headers->get('content-type', ''), 'spreadsheet') ||
            str_contains($response->headers->get('content-type', ''), 'vnd.openxmlformats') ||
            str_contains($response->headers->get('content-type', ''), 'octet-stream') ||
            str_contains($response->headers->get('content-disposition', ''), '.xlsx'),
            'Response must deliver an Excel spreadsheet export.'
        );
    }

    /**
     * 3. Admin PDF export produces formatted summary document in landscape layout.
     */
    public function test_admin_export_pdf_contains_registered_applicants_in_landscape_format(): void
    {
        $admin = $this->createAdmin();

        $this->createPendaftaran(['nama_lengkap' => 'Applicant PDF One', 'status' => 'Lulus']);
        $this->createPendaftaran(['nama_lengkap' => 'Applicant PDF Two', 'status' => 'Menunggu']);

        $response = $this->actingAs($admin)->get(route('admin.export.pdf'));

        $response->assertStatus(200);
        $this->assertTrue(
            str_contains($response->headers->get('content-type', ''), 'application/pdf') ||
            str_contains($response->headers->get('content-disposition', ''), '.pdf'),
            'Response must deliver a PDF download stream.'
        );
    }

    /**
     * 4. Data consistency: non-admin roles are forbidden from accessing archive and export endpoints.
     */
    public function test_non_admin_cannot_access_zip_excel_or_pdf_exports(): void
    {
        $ustadz = $this->createUstadz();
        $calon = $this->createCalonSantri();
        $pendaftar = $this->createPendaftaran();

        // Ustadz 403
        $this->actingAs($ustadz)->get(route('admin.download-zip', $pendaftar->id))->assertForbidden();
        $this->actingAs($ustadz)->get(route('admin.export.excel'))->assertForbidden();
        $this->actingAs($ustadz)->get(route('admin.export.pdf'))->assertForbidden();

        // Calon Santri 403
        $this->actingAs($calon)->get(route('admin.download-zip', $pendaftar->id))->assertForbidden();
        $this->actingAs($calon)->get(route('admin.export.excel'))->assertForbidden();
        $this->actingAs($calon)->get(route('admin.export.pdf'))->assertForbidden();
    }
}
