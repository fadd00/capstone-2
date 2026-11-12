<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\Santri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 30: CRUD Wali Murid (F30)
 *
 * Source: dokum-cp1 §5.2 Q-ADM-01, Build Plan §6.3
 * Minimum 5 test methods covering:
 * - Listing wali murid accounts
 * - Creating wali murid account with 'wali_murid' Spatie role
 * - Validation on duplicate email
 * - Viewing wali murid detail with children list
 * - Updating wali murid info
 * - Deleting wali murid and confirming ON DELETE SET NULL on santri
 * - Role-based authorization guard (403 for non-admin)
 */
class F30_WaliMuridCrudTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    public function test_admin_can_view_wali_murid_index_page(): void
    {
        $admin = $this->createAdmin();
        $wali = $this->createWaliMurid(['name' => 'Bapak Ahmad Sulaiman']);

        $response = $this->actingAs($admin)->get(route('admin.wali-murid.index'));

        $response->assertOk();
        $response->assertSee('Bapak Ahmad Sulaiman');
    }

    public function test_admin_can_create_new_wali_murid_account(): void
    {
        $admin = $this->createAdmin();

        $payload = [
            'name' => 'Ibu Siti Aminah',
            'email' => 'siti_aminah@pptq.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($admin)->post(route('admin.wali-murid.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.wali-murid.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Ibu Siti Aminah',
            'email' => 'siti_aminah@pptq.test',
        ]);

        $wali = User::where('email', 'siti_aminah@pptq.test')->firstOrFail();
        $this->assertTrue($wali->hasRole('wali_murid'));
    }

    public function test_create_wali_murid_validation_fails_on_duplicate_email(): void
    {
        $admin = $this->createAdmin();
        $this->createWaliMurid(['email' => 'wali_existing@pptq.test']);

        $response = $this->actingAs($admin)->post(route('admin.wali-murid.store'), [
            'name' => 'Duplicate Wali',
            'email' => 'wali_existing@pptq.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_admin_can_view_wali_murid_detail_with_associated_children(): void
    {
        $admin = $this->createAdmin();
        $wali = $this->createWaliMurid(['name' => 'Bapak Hendra']);
        $santri = Santri::create([
            'nama' => 'Anak Hendra Pratama',
            'nis' => '2026020',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
            'wali_murid_id' => $wali->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.wali-murid.show', $wali->id));

        $response->assertOk();
        $response->assertSee('Bapak Hendra');
        $response->assertSee('Anak Hendra Pratama');
    }

    public function test_admin_can_update_wali_murid_details(): void
    {
        $admin = $this->createAdmin();
        $wali = $this->createWaliMurid([
            'name' => 'Bapak Budi Hartono',
            'email' => 'budi_hartono@pptq.test',
        ]);

        $updatePayload = [
            'name' => 'Bapak Budi Hartono S.T.',
            'email' => 'budi_hartono_updated@pptq.test',
        ];

        $response = $this->actingAs($admin)->put(route('admin.wali-murid.update', $wali->id), $updatePayload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $wali->id,
            'name' => 'Bapak Budi Hartono S.T.',
            'email' => 'budi_hartono_updated@pptq.test',
        ]);
    }

    public function test_admin_can_delete_wali_murid_and_santri_foreign_key_is_set_to_null(): void
    {
        $admin = $this->createAdmin();
        $wali = $this->createWaliMurid();
        $santri = Santri::create([
            'nama' => 'Santri Orphan Test',
            'nis' => '2026021',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
            'wali_murid_id' => $wali->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.wali-murid.destroy', $wali->id));

        $response->assertRedirect(route('admin.wali-murid.index'));
        $this->assertDatabaseMissing('users', ['id' => $wali->id]);

        $santri->refresh();
        $this->assertNull($santri->wali_murid_id, 'Expected santri.wali_murid_id to be set to NULL upon parent deletion');
    }

    public function test_non_admin_cannot_manage_wali_murid(): void
    {
        $ustadz = $this->createUstadz();
        $wali = $this->createWaliMurid();
        $calon = $this->createCalonSantri();

        $this->actingAs($ustadz)->get(route('admin.wali-murid.index'))->assertForbidden();
        $this->actingAs($wali)->get(route('admin.wali-murid.index'))->assertForbidden();
        $this->actingAs($calon)->get(route('admin.wali-murid.index'))->assertForbidden();
    }
}
