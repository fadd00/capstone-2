<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 11: Auto-Generate Applicant Account (F11)
 *
 * Source: dokum-ppdb §6.2, Build Plan §7.4
 * Verifies automatic creation of User record with random 8-char password,
 * Spatie 'calon_santri' role assignment, and linking to pendaftarans.user_id.
 */
#[\PHPUnit\Framework\Attributes\Group('retired-ppdb')]
class F11_AutoGenerateApplicantAccountTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, RefreshDatabase;

    public function test_user_account_automatically_created_with_role_calon_santri_upon_registration(): void
    {
        Storage::fake('local');

        Role::firstOrCreate(['name' => 'calon_santri', 'guard_name' => 'web']);

        $formData = $this->validPpdbFormData([
            'nama_lengkap' => 'Muhammad Bilal Al-Habsyi',
            'email' => 'bilal_habsyi@example.com',
        ]);
        $files = $this->fakeRequiredPpdbFiles();

        $response = $this->post(route('pendaftaran.store'), array_merge($formData, $files));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('register.success'));

        $this->assertDatabaseHas('users', [
            'name' => 'Muhammad Bilal Al-Habsyi',
            'email' => 'bilal_habsyi@example.com',
        ]);

        $user = User::where('email', 'bilal_habsyi@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('calon_santri'));
    }

    public function test_pendaftaran_record_is_linked_to_the_newly_created_user_id(): void
    {
        Storage::fake('local');
        Role::firstOrCreate(['name' => 'calon_santri', 'guard_name' => 'web']);

        $formData = $this->validPpdbFormData([
            'email' => 'calon_link_check@example.com',
        ]);
        $files = $this->fakeRequiredPpdbFiles();

        $this->post(route('pendaftaran.store'), array_merge($formData, $files));

        $user = User::where('email', 'calon_link_check@example.com')->firstOrFail();
        $pendaftaran = Pendaftaran::where('email', 'calon_link_check@example.com')->firstOrFail();

        $this->assertEquals($user->id, $pendaftaran->user_id);
    }

    public function test_registration_fails_if_email_is_already_registered_in_users_table(): void
    {
        Storage::fake('local');

        // Existing user with this email
        User::create([
            'name' => 'Existing User',
            'email' => 'duplicate@example.com',
            'password' => bcrypt('password123'),
        ]);

        $formData = $this->validPpdbFormData([
            'email' => 'duplicate@example.com',
        ]);
        $files = $this->fakeRequiredPpdbFiles();

        $response = $this->post(route('pendaftaran.store'), array_merge($formData, $files));

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('pendaftarans', [
            'email' => 'duplicate@example.com',
        ]);
    }

    public function test_session_contains_generated_credentials_on_successful_registration(): void
    {
        Storage::fake('local');
        Role::firstOrCreate(['name' => 'calon_santri', 'guard_name' => 'web']);

        $formData = $this->validPpdbFormData([
            'email' => 'credentials_session@example.com',
        ]);
        $files = $this->fakeRequiredPpdbFiles();

        $response = $this->post(route('pendaftaran.store'), array_merge($formData, $files));

        $response->assertSessionHas('email', 'credentials_session@example.com');
        $response->assertSessionHas('password');

        $generatedPassword = session('password');
        $this->assertIsString($generatedPassword);
        $this->assertGreaterThanOrEqual(8, strlen($generatedPassword));
    }

    public function test_initial_status_of_created_pendaftaran_is_menunggu(): void
    {
        Storage::fake('local');
        Role::firstOrCreate(['name' => 'calon_santri', 'guard_name' => 'web']);

        $formData = $this->validPpdbFormData([
            'email' => 'initial_status@example.com',
        ]);
        $files = $this->fakeRequiredPpdbFiles();

        $this->post(route('pendaftaran.store'), array_merge($formData, $files));

        $this->assertDatabaseHas('pendaftarans', [
            'email' => 'initial_status@example.com',
            'status' => 'Menunggu',
        ]);
    }
}
