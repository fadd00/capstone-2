<?php

namespace Tests\Feature\Tier2_Boundary;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Tier 2 Boundary Test: B03 File Upload Boundary
 *
 * Source: dokum-ppdb §9.2, Build Plan §7.2
 * Verifies file upload boundary limits:
 * - Exact 2048 KB (2MB) file accepted
 * - 2049 KB file rejected with 422 / validation error
 * - 0-byte empty file rejected
 * - MIME spoofing (PHP script renamed as .pdf) rejected
 * - Missing required files rejected while omitting 3 optional files accepted
 * - Disallowed executable extensions rejected
 */
class B03_FileUploadBoundaryTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->createStatusForm('Buka');
    }

    /**
     * 1. Uploaded file at exact 2048 KB limit is accepted.
     */
    public function test_file_upload_at_exact_2048_kb_limit_is_accepted(): void
    {
        $exact2MbFile = UploadedFile::fake()->create('exact_limit.pdf', 2048, 'application/pdf');

        $files = $this->fakeRequiredPpdbFiles([
            'bukti_transfer' => $exact2MbFile,
        ]);

        $payload = array_merge($this->validPpdbFormData(), $files);

        $response = $this->post(route('pendaftaran.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('register.success'));
    }

    /**
     * 2. Uploaded file exceeding limit by 1 KB (2049 KB) is rejected with validation error.
     */
    public function test_file_upload_exceeding_limit_at_2049_kb_is_rejected(): void
    {
        $exceedingFile = UploadedFile::fake()->create('too_large.pdf', 2049, 'application/pdf');

        $files = $this->fakeRequiredPpdbFiles([
            'bukti_transfer' => $exceedingFile,
        ]);

        $payload = array_merge($this->validPpdbFormData(), $files);

        $response = $this->post(route('pendaftaran.store'), $payload);

        $response->assertSessionHasErrors(['bukti_transfer']);
    }

    /**
     * 3. 0-byte empty file upload is rejected by validation.
     */
    public function test_zero_byte_empty_file_upload_is_rejected(): void
    {
        $emptyFile = UploadedFile::fake()->create('empty.pdf', 0, 'application/pdf');

        $files = $this->fakeRequiredPpdbFiles([
            'akta_kelahiran' => $emptyFile,
        ]);

        $payload = array_merge($this->validPpdbFormData(), $files);

        $response = $this->post(route('pendaftaran.store'), $payload);

        $response->assertSessionHasErrors(['akta_kelahiran']);
    }

    /**
     * 4. MIME type spoofing (executable PHP script renamed to .pdf) is rejected.
     */
    public function test_mime_type_spoofing_php_script_renamed_to_pdf_is_rejected(): void
    {
        // Malicious script with .pdf name but text/x-php mime type
        $maliciousFile = UploadedFile::fake()->createWithContent(
            'malicious.pdf',
            '<?php phpinfo(); ?>'
        );

        $files = $this->fakeRequiredPpdbFiles([
            'kartu_keluarga' => $maliciousFile,
        ]);

        $payload = array_merge($this->validPpdbFormData(), $files);

        $response = $this->post(route('pendaftaran.store'), $payload);

        $response->assertSessionHasErrors(['kartu_keluarga']);
    }

    /**
     * 5. Missing mandatory files triggers validation errors, but omitting optional files is accepted.
     */
    public function test_missing_required_files_fails_validation_while_missing_optional_files_succeeds(): void
    {
        // Omitting 3 optional files ('skhun', 'sktm', 'ijazah_terakhir')
        $requiredOnlyFiles = $this->fakeRequiredPpdbFiles();

        $payload = array_merge($this->validPpdbFormData(), $requiredOnlyFiles);

        $response = $this->post(route('pendaftaran.store'), $payload);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('register.success'));

        // Now test omitting one required file ('pas_photo')
        unset($requiredOnlyFiles['pas_photo']);
        $invalidPayload = array_merge($this->validPpdbFormData(), $requiredOnlyFiles);

        $responseInvalid = $this->post(route('pendaftaran.store'), $invalidPayload);
        $responseInvalid->assertSessionHasErrors(['pas_photo']);
    }

    /**
     * 6. Disallowed extensions like .exe, .sh, or .bat are rejected.
     */
    public function test_disallowed_extensions_are_rejected(): void
    {
        $disallowedFiles = [
            'payload.exe' => UploadedFile::fake()->create('payload.exe', 100, 'application/x-msdownload'),
            'script.sh' => UploadedFile::fake()->create('script.sh', 50, 'text/x-shellscript'),
        ];

        foreach ($disallowedFiles as $filename => $badFile) {
            $files = $this->fakeRequiredPpdbFiles([
                'formulir_pendaftaran' => $badFile,
            ]);

            $payload = array_merge($this->validPpdbFormData(), $files);

            $response = $this->post(route('pendaftaran.store'), $payload);
            $response->assertSessionHasErrors(['formulir_pendaftaran']);
        }
    }
}
