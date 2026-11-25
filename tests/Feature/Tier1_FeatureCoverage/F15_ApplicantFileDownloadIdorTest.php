<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 15: Unduh Berkas Pribadi (IDOR Safe) (F15)
 *
 * Source: dokum-ppdb §6.4, Build Plan §7.5 (Decision D-05)
 * Verifies secure file download at /user/pendaftar/{id}/file/{field} with strict
 * authorization checking (auth()->id() === user_id) preventing IDOR data leaks.
 */
#[\PHPUnit\Framework\Attributes\Group('retired-ppdb')]
class F15_ApplicantFileDownloadIdorTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, RefreshDatabase;

    public function test_calon_santri_can_download_their_own_file_successfully(): void
    {
        Storage::fake('local');

        $calonSantri = $this->createCalonSantri();
        $filePath = 'pendaftaran/pas_photo/my_photo.jpg';
        Storage::disk('local')->put($filePath, 'fake image content bytes');

        $pendaftaran = $this->createPendaftaran([
            'user_id' => $calonSantri->id,
            'pas_photo' => $filePath,
        ], $calonSantri);

        $response = $this->actingAs($calonSantri)->get(route('user.file', [
            'id' => $pendaftaran->id,
            'field' => 'pas_photo',
        ]));

        $response->assertStatus(200);
    }

    public function test_calon_santri_blocked_with_403_when_accessing_another_applicants_file_idor(): void
    {
        Storage::fake('local');

        $victimUser = $this->createCalonSantri(['email' => 'victim@example.com']);
        $attackerUser = $this->createCalonSantri(['email' => 'attacker@example.com']);

        $filePath = 'pendaftaran/kartu_keluarga/victim_kk.pdf';
        Storage::disk('local')->put($filePath, 'confidential family card');

        $victimPendaftaran = $this->createPendaftaran([
            'user_id' => $victimUser->id,
            'kartu_keluarga' => $filePath,
        ], $victimUser);

        // Attacker attempts to download victim's sensitive file
        $response = $this->actingAs($attackerUser)->get(route('user.file', [
            'id' => $victimPendaftaran->id,
            'field' => 'kartu_keluarga',
        ]));

        $response->assertStatus(403);
    }

    public function test_admin_can_download_applicant_file_via_admin_file_route(): void
    {
        Storage::fake('local');

        $admin = $this->createAdmin();
        $calonSantri = $this->createCalonSantri();

        $filePath = 'pendaftaran/bukti_transfer/transfer_123.pdf';
        Storage::disk('local')->put($filePath, 'receipt slip pdf');

        $pendaftaran = $this->createPendaftaran([
            'user_id' => $calonSantri->id,
            'bukti_transfer' => $filePath,
        ], $calonSantri);

        $response = $this->actingAs($admin)->get(route('admin.file', [
            'id' => $pendaftaran->id,
            'field' => 'bukti_transfer',
        ]));

        $response->assertStatus(200);
    }

    public function test_download_returns_404_when_file_does_not_exist_on_storage(): void
    {
        Storage::fake('local');

        $calonSantri = $this->createCalonSantri();
        $pendaftaran = $this->createPendaftaran([
            'user_id' => $calonSantri->id,
            'akta_kelahiran' => 'pendaftaran/akta_kelahiran/missing_file.pdf',
        ], $calonSantri);

        $response = $this->actingAs($calonSantri)->get(route('user.file', [
            'id' => $pendaftaran->id,
            'field' => 'akta_kelahiran',
        ]));

        $response->assertStatus(404);
    }

    public function test_unauthenticated_guest_is_redirected_to_login_on_download_attempt(): void
    {
        $calonSantri = $this->createCalonSantri();
        $pendaftaran = $this->createPendaftaran([], $calonSantri);

        $response = $this->get(route('user.file', [
            'id' => $pendaftaran->id,
            'field' => 'pas_photo',
        ]));

        $response->assertRedirect(route('login'));
    }

    public function test_download_rejects_arbitrary_non_file_fields(): void
    {
        $calonSantri = $this->createCalonSantri();
        $pendaftaran = $this->createPendaftaran([], $calonSantri);

        // Attempt to pass 'nama_lengkap' or 'email' as the file field
        $response = $this->actingAs($calonSantri)->get(route('user.file', [
            'id' => $pendaftaran->id,
            'field' => 'nama_lengkap',
        ]));

        $this->assertContains($response->getStatusCode(), [400, 403, 404]);
    }
}
