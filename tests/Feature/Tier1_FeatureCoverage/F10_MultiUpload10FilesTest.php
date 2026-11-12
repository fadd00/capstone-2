<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\Pendaftaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 10: Multi-Upload 10 Berkas (Private Storage) (F10)
 *
 * Source: dokum-ppdb §9.2, Build Plan §7.3
 * Verifies upload validation for 7 mandatory files and 3 optional files,
 * 2048 KB limit, allowed MIME types (PDF, JPG, JPEG, PNG), and private storage isolation.
 */
class F10_MultiUpload10FilesTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, RefreshDatabase;

    public function test_submission_with_all_10_valid_files_stores_files_in_private_storage(): void
    {
        Storage::fake('local');

        $formData = $this->validPpdbFormData([
            'email' => 'calon_lengkap@example.com',
        ]);
        $files = $this->fakePpdbFiles();

        $response = $this->post(route('pendaftaran.store'), array_merge($formData, $files));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('register.success'));

        $this->assertDatabaseHas('pendaftarans', [
            'email' => 'calon_lengkap@example.com',
        ]);

        $pendaftar = Pendaftaran::where('email', 'calon_lengkap@example.com')->firstOrFail();

        foreach (array_keys($files) as $field) {
            $this->assertNotNull($pendaftar->{$field}, "Field {$field} should have a saved file path.");
            Storage::disk('local')->assertExists($pendaftar->{$field});
        }
    }

    public function test_submission_with_only_7_mandatory_files_succeeds_with_nullable_optionals(): void
    {
        Storage::fake('local');

        $formData = $this->validPpdbFormData([
            'email' => 'calon_mandatory_only@example.com',
        ]);
        $mandatoryFiles = $this->fakeRequiredPpdbFiles();

        $response = $this->post(route('pendaftaran.store'), array_merge($formData, $mandatoryFiles));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('register.success'));

        $pendaftar = Pendaftaran::where('email', 'calon_mandatory_only@example.com')->firstOrFail();

        // 7 mandatory must exist
        foreach (array_keys($mandatoryFiles) as $field) {
            $this->assertNotNull($pendaftar->{$field});
            Storage::disk('local')->assertExists($pendaftar->{$field});
        }

        // 3 optional fields should be null
        $this->assertNull($pendaftar->ijazah_terakhir);
        $this->assertNull($pendaftar->skhun);
        $this->assertNull($pendaftar->sktm);
    }

    public function test_submission_fails_validation_when_any_mandatory_file_is_missing(): void
    {
        Storage::fake('local');

        $formData = $this->validPpdbFormData();
        $files = $this->fakeRequiredPpdbFiles();
        unset($files['pas_photo']); // Remove mandatory pas_photo

        $response = $this->post(route('pendaftaran.store'), array_merge($formData, $files));

        $response->assertSessionHasErrors('pas_photo');
        $this->assertDatabaseMissing('pendaftarans', [
            'email' => $formData['email'],
        ]);
    }

    public function test_submission_fails_validation_when_file_exceeds_2048_kb(): void
    {
        Storage::fake('local');

        $formData = $this->validPpdbFormData();
        $files = $this->fakeRequiredPpdbFiles([
            'bukti_transfer' => UploadedFile::fake()->create('bukti_besar.pdf', 2500, 'application/pdf'), // 2.5 MB > 2 MB
        ]);

        $response = $this->post(route('pendaftaran.store'), array_merge($formData, $files));

        $response->assertSessionHasErrors('bukti_transfer');
        $this->assertDatabaseMissing('pendaftarans', [
            'email' => $formData['email'],
        ]);
    }

    public function test_submission_fails_validation_for_disallowed_file_types(): void
    {
        Storage::fake('local');

        $formData = $this->validPpdbFormData();
        $files = $this->fakeRequiredPpdbFiles([
            'formulir_pendaftaran' => UploadedFile::fake()->create('malicious.sh', 100, 'application/x-sh'),
        ]);

        $response = $this->post(route('pendaftaran.store'), array_merge($formData, $files));

        $response->assertSessionHasErrors('formulir_pendaftaran');
        $this->assertDatabaseMissing('pendaftarans', [
            'email' => $formData['email'],
        ]);
    }

    public function test_uploaded_files_are_stored_on_local_private_disk_never_public_disk(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $formData = $this->validPpdbFormData([
            'email' => 'calon_privacy@example.com',
        ]);
        $files = $this->fakeRequiredPpdbFiles();

        $response = $this->post(route('pendaftaran.store'), array_merge($formData, $files));

        $response->assertSessionHasNoErrors();

        $pendaftar = Pendaftaran::where('email', 'calon_privacy@example.com')->firstOrFail();

        // Must be in local (private) disk
        Storage::disk('local')->assertExists($pendaftar->pas_photo);

        // Must NOT be in public disk
        Storage::disk('public')->assertMissing($pendaftar->pas_photo);
    }
}
