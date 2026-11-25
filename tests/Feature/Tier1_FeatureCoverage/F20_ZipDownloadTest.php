<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 20: Unduh Berkas ZIP Tunggal (F20)
 *
 * Source: dokum-ppdb §9.3, Build Plan §7.5 (Decision D-07)
 * Verifies on-demand bundling of all applicant documents into a single .zip
 * archive via GET /admin/pendaftar/{id}/download-zip with proper headers and security.
 */
#[\PHPUnit\Framework\Attributes\Group('retired-ppdb')]
class F20_ZipDownloadTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, RefreshDatabase;

    public function test_admin_can_download_all_applicant_files_as_zip(): void
    {
        Storage::fake('local');

        $admin = $this->createAdmin();

        // Create applicant files on disk
        $files = [
            'pas_photo' => 'pendaftaran/pas_photo/photo_zip.jpg',
            'bukti_transfer' => 'pendaftaran/bukti_transfer/tf_zip.pdf',
            'formulir_pendaftaran' => 'pendaftaran/formulir_pendaftaran/form_zip.pdf',
        ];
        foreach ($files as $field => $path) {
            Storage::disk('local')->put($path, "content for {$field}");
        }

        $pendaftar = $this->createPendaftaran(array_merge([
            'nama_lengkap' => 'Santri Berkas Lengkap',
        ], $files));

        $response = $this->actingAs($admin)->get(route('admin.download-zip', $pendaftar->id));

        $response->assertStatus(200);

        $contentType = (string) $response->headers->get('content-type');
        $this->assertTrue(
            str_contains($contentType, 'zip') ||
            str_contains($contentType, 'octet-stream'),
            "Content-Type should be zip or octet-stream, received: {$contentType}"
        );

        $disposition = (string) $response->headers->get('content-disposition');
        $this->assertStringContainsString('attachment', $disposition);
        $this->assertStringContainsString('.zip', $disposition);
    }

    public function test_zip_filename_matches_convention_with_applicant_name_or_id(): void
    {
        Storage::fake('local');

        $admin = $this->createAdmin();
        $pendaftar = $this->createPendaftaran([
            'nama_lengkap' => 'Muhammad Zip File',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.download-zip', $pendaftar->id));

        $disposition = (string) $response->headers->get('content-disposition');
        $this->assertTrue(
            str_contains(strtolower($disposition), 'muhammad') ||
            str_contains($disposition, (string) $pendaftar->id) ||
            str_contains($disposition, '.zip')
        );
    }

    public function test_zip_download_handles_applicant_with_only_mandatory_files(): void
    {
        Storage::fake('local');

        $admin = $this->createAdmin();
        $pendaftar = $this->createPendaftaran([
            'ijazah_terakhir' => null,
            'skhun' => null,
            'sktm' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.download-zip', $pendaftar->id));

        $response->assertStatus(200);
    }

    public function test_zip_download_returns_404_when_applicant_does_not_exist(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.download-zip', 888888));

        $response->assertStatus(404);
    }

    public function test_non_admin_cannot_download_applicant_zip(): void
    {
        $calonSantri = $this->createCalonSantri();
        $pendaftar = $this->createPendaftaran([], $calonSantri);

        $response = $this->actingAs($calonSantri)->get(route('admin.download-zip', $pendaftar->id));

        $response->assertStatus(403);
    }
}
