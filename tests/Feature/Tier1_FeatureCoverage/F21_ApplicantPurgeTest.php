<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 21: Hapus & Purge Pendaftar (F21)
 *
 * Source: dokum-ppdb §5.3 Q4, Q5, Build Plan §6.4
 * Verifies single applicant deletion and transactional bulk purge (hapus-semua)
 * including clean cleanup of files and applicant user accounts.
 */
#[\PHPUnit\Framework\Attributes\Group('retired-ppdb')]
class F21_ApplicantPurgeTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, RefreshDatabase;

    public function test_admin_can_delete_single_applicant_and_associated_user_and_files(): void
    {
        Storage::fake('local');

        $admin = $this->createAdmin();
        $calonUser = $this->createCalonSantri(['email' => 'calon_to_delete@example.com']);

        $filePath = 'pendaftaran/pas_photo/to_delete.jpg';
        Storage::disk('local')->put($filePath, 'sample photo bytes');

        $pendaftar = $this->createPendaftaran([
            'user_id' => $calonUser->id,
            'email' => $calonUser->email,
            'pas_photo' => $filePath,
        ], $calonUser);

        $response = $this->actingAs($admin)->delete(route('admin.destroy', $pendaftar->id));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.ppdb.index'));

        // Pendaftaran must be deleted
        $this->assertDatabaseMissing('pendaftarans', ['id' => $pendaftar->id]);

        // Calon santri user account must be deleted
        $this->assertDatabaseMissing('users', ['id' => $calonUser->id]);

        // File must be purged from storage
        Storage::disk('local')->assertMissing($filePath);
    }

    public function test_admin_can_purge_all_applicants_via_destroy_all_endpoint(): void
    {
        Storage::fake('local');

        $admin = $this->createAdmin();

        $this->createPendaftaran(['nama_lengkap' => 'Santri 1']);
        $this->createPendaftaran(['nama_lengkap' => 'Santri 2']);
        $this->createPendaftaran(['nama_lengkap' => 'Santri 3']);

        $this->assertEquals(3, Pendaftaran::count());

        $response = $this->actingAs($admin)->delete(route('admin.destroyAll'));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.ppdb.index'));

        $this->assertEquals(0, Pendaftaran::count());
    }

    public function test_purge_preserves_admin_and_staff_accounts_only_clearing_applicants(): void
    {
        $admin = $this->createAdmin(['email' => 'admin_kept@ibnujuraimi.sch.id']);
        $ustadz = $this->createUstadz(['email' => 'ustadz_kept@ibnujuraimi.sch.id']);
        $wali = $this->createWaliMurid(['email' => 'wali_kept@ibnujuraimi.sch.id']);

        $calon = $this->createCalonSantri(['email' => 'calon_purged@ibnujuraimi.sch.id']);
        $this->createPendaftaran(['email' => $calon->email, 'user_id' => $calon->id], $calon);

        $this->actingAs($admin)->delete(route('admin.destroyAll'));

        // Non-applicant accounts must survive
        $this->assertDatabaseHas('users', ['email' => 'admin_kept@ibnujuraimi.sch.id']);
        $this->assertDatabaseHas('users', ['email' => 'ustadz_kept@ibnujuraimi.sch.id']);
        $this->assertDatabaseHas('users', ['email' => 'wali_kept@ibnujuraimi.sch.id']);

        // Pendaftaran must be empty
        $this->assertEquals(0, Pendaftaran::count());
    }

    public function test_deleting_non_existent_applicant_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->delete(route('admin.destroy', 777777));

        $response->assertStatus(404);
    }

    public function test_non_admin_cannot_delete_or_purge_applicants(): void
    {
        $calonSantri = $this->createCalonSantri();
        $pendaftar = $this->createPendaftaran([], $calonSantri);

        $this->actingAs($calonSantri)->delete(route('admin.destroy', $pendaftar->id))->assertStatus(403);
        $this->actingAs($calonSantri)->delete(route('admin.destroyAll'))->assertStatus(403);

        $this->assertDatabaseHas('pendaftarans', ['id' => $pendaftar->id]);
    }
}
