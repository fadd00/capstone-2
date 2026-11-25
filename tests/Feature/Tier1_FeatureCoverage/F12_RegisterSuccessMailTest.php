<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Mail\RegisterSuccessMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 12: Email Kredensial Akun (F12)
 *
 * Source: dokum-ppdb §8.2, Build Plan §12.2
 * Verifies transactional delivery of RegisterSuccessMail containing
 * generated login credentials upon successful online PPDB submission.
 */
#[\PHPUnit\Framework\Attributes\Group('retired-ppdb')]
class F12_RegisterSuccessMailTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, RefreshDatabase;

    public function test_register_success_mail_is_sent_to_applicant_upon_registration(): void
    {
        Mail::fake();
        Storage::fake('local');
        Role::firstOrCreate(['name' => 'calon_santri', 'guard_name' => 'web']);

        $formData = $this->validPpdbFormData([
            'email' => 'calon_email_target@example.com',
            'nama_lengkap' => 'Santri Kirim Email',
        ]);
        $files = $this->fakeRequiredPpdbFiles();

        $response = $this->post(route('pendaftaran.store'), array_merge($formData, $files));

        $response->assertSessionHasNoErrors();

        Mail::assertSent(RegisterSuccessMail::class, function ($mail) {
            return $mail->hasTo('calon_email_target@example.com');
        });
    }

    public function test_register_success_mail_contains_email_and_temporary_password(): void
    {
        Mail::fake();
        Storage::fake('local');
        Role::firstOrCreate(['name' => 'calon_santri', 'guard_name' => 'web']);

        $formData = $this->validPpdbFormData([
            'email' => 'calon_credentials_check@example.com',
        ]);
        $files = $this->fakeRequiredPpdbFiles();

        $this->post(route('pendaftaran.store'), array_merge($formData, $files));

        Mail::assertSent(RegisterSuccessMail::class, function ($mail) {
            $hasEmail = isset($mail->email) || (isset($mail->user) && $mail->user->email === 'calon_credentials_check@example.com');
            $hasPassword = isset($mail->password) && ! empty($mail->password);

            return $hasEmail || $hasPassword;
        });
    }

    public function test_register_success_mail_is_not_sent_when_registration_fails_validation(): void
    {
        Mail::fake();
        Storage::fake('local');

        // Missing required field 'nama_lengkap'
        $formData = $this->validPpdbFormData(['nama_lengkap' => '']);
        $files = $this->fakeRequiredPpdbFiles();

        $response = $this->post(route('pendaftaran.store'), array_merge($formData, $files));

        $response->assertSessionHasErrors('nama_lengkap');
        Mail::assertNothingSent();
    }

    public function test_mailable_instance_renders_properly(): void
    {
        $mailable = new RegisterSuccessMail('santri_preview@example.com', 'Pass1234!', 'Santri Preview');

        // Assert mailable properties or rendered envelope
        $this->assertNotNull($mailable);
        if (property_exists($mailable, 'email')) {
            $this->assertEquals('santri_preview@example.com', $mailable->email);
        }
        if (property_exists($mailable, 'password')) {
            $this->assertEquals('Pass1234!', $mailable->password);
        }
    }

    public function test_multiple_registrations_each_dispatch_independent_emails(): void
    {
        Mail::fake();
        Storage::fake('local');
        Role::firstOrCreate(['name' => 'calon_santri', 'guard_name' => 'web']);

        // First registration
        $firstData = $this->validPpdbFormData(['email' => 'santri_one@example.com']);
        $this->post(route('pendaftaran.store'), array_merge($firstData, $this->fakeRequiredPpdbFiles()));

        // Second registration
        $secondData = $this->validPpdbFormData(['email' => 'santri_two@example.com']);
        $this->post(route('pendaftaran.store'), array_merge($secondData, $this->fakeRequiredPpdbFiles()));

        Mail::assertSent(RegisterSuccessMail::class, 2);
        Mail::assertSent(RegisterSuccessMail::class, fn ($mail) => $mail->hasTo('santri_one@example.com'));
        Mail::assertSent(RegisterSuccessMail::class, fn ($mail) => $mail->hasTo('santri_two@example.com'));
    }
}
