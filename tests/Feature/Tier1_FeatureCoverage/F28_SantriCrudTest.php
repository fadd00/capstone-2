<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\Santri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 28: CRUD Santri (Akademik) (F28)
 *
 * Source: dokum-cp1 §5.2 Q-ADM-06, 07, Build Plan §6.3
 * Minimum 5 test methods covering:
 * - Listing santri
 * - Creating santri with standard/alphanumeric NIS
 * - Validation on duplicate NIS and missing fields
 * - Updating santri attributes
 * - Deleting santri
 * - Role-based authorization guard (403 for non-admin)
 */
class F28_SantriCrudTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    public function test_admin_can_view_santri_index_page(): void
    {
        $admin = $this->createAdmin();
        $santri = Santri::create([
            'nama' => 'Muhammad Hafizh',
            'nis' => '2026001',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.santri.index'));

        $response->assertOk();
        $response->assertSee('Muhammad Hafizh');
        $response->assertSee('2026001');
    }

    public function test_admin_can_create_santri_with_valid_nis_string(): void
    {
        $admin = $this->createAdmin();
        $wali = $this->createWaliMurid(['name' => 'Bapak Ahmad']);

        $payload = [
            'nama' => 'Ahmad Fauzi',
            'nis' => '2026002',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7B',
            'wali_murid_id' => $wali->id,
            'tanggal_lahir' => '2012-08-20',
        ];

        $response = $this->actingAs($admin)->post(route('admin.santri.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.santri.index'));

        $this->assertDatabaseHas('santri', [
            'nama' => 'Ahmad Fauzi',
            'nis' => '2026002',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7B',
            'wali_murid_id' => $wali->id,
            'tanggal_lahir' => '2012-08-20',
        ]);
    }

    public function test_create_santri_validation_fails_on_duplicate_nis_or_missing_required_fields(): void
    {
        $admin = $this->createAdmin();
        Santri::create([
            'nama' => 'Santri Eksis',
            'nis' => '2026003',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);

        // Duplicate NIS
        $responseDuplicate = $this->actingAs($admin)->post(route('admin.santri.store'), [
            'nama' => 'Santri Duplikat',
            'nis' => '2026003',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '8',
        ]);
        $responseDuplicate->assertSessionHasErrors(['nis']);

        // Missing required nama & tingkatan
        $responseMissing = $this->actingAs($admin)->post(route('admin.santri.store'), [
            'nama' => '',
            'nis' => '2026004',
            'tingkatan' => 'invalid_grade',
        ]);
        $responseMissing->assertSessionHasErrors(['nama', 'tingkatan']);
    }

    public function test_admin_can_view_santri_detail_and_edit_page(): void
    {
        $admin = $this->createAdmin();
        $santri = Santri::create([
            'nama' => 'Zaid bin Tsabit',
            'nis' => '2026005',
            'tingkatan' => 'takhassus',
            'kelas' => 'Takhassus 1',
        ]);

        $responseShow = $this->actingAs($admin)->get(route('admin.santri.show', $santri->id));
        $responseShow->assertOk();
        $responseShow->assertSee('Zaid bin Tsabit');

        $responseEdit = $this->actingAs($admin)->get(route('admin.santri.edit', $santri->id));
        $responseEdit->assertOk();
        $responseEdit->assertSee('Zaid bin Tsabit');
    }

    public function test_admin_can_update_santri_attributes(): void
    {
        $admin = $this->createAdmin();
        $santri = Santri::create([
            'nama' => 'Fatimah Az-Zahra',
            'nis' => '2026006',
            'tingkatan' => 'aliyah',
            'kelas' => '10',
        ]);

        $updatePayload = [
            'nama' => 'Fatimah Az-Zahra Al-Anshari',
            'nis' => '2026006',
            'tingkatan' => 'aliyah',
            'kelas' => '11 IPA',
        ];

        $response = $this->actingAs($admin)->put(route('admin.santri.update', $santri->id), $updatePayload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('santri', [
            'id' => $santri->id,
            'nama' => 'Fatimah Az-Zahra Al-Anshari',
            'kelas' => '11 IPA',
        ]);
    }

    public function test_admin_can_delete_santri(): void
    {
        $admin = $this->createAdmin();
        $santri = Santri::create([
            'nama' => 'Santri Mau Dihapus',
            'nis' => '2026007',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.santri.destroy', $santri->id));

        $response->assertRedirect(route('admin.santri.index'));
        $this->assertDatabaseMissing('santri', ['id' => $santri->id]);
    }

    public function test_unauthorized_roles_cannot_manage_santri(): void
    {
        $ustadz = $this->createUstadz();
        $wali = $this->createWaliMurid();
        $calon = $this->createCalonSantri();

        $this->actingAs($ustadz)->get(route('admin.santri.index'))->assertForbidden();
        $this->actingAs($wali)->get(route('admin.santri.index'))->assertForbidden();
        $this->actingAs($calon)->get(route('admin.santri.index'))->assertForbidden();

        $this->actingAs($ustadz)->post(route('admin.santri.store'), [
            'nama' => 'Illegal Santri',
            'nis' => '2026999',
            'tingkatan' => 'tsanawiyah',
        ])->assertForbidden();
    }
}
