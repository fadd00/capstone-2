<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\KegiatanPondok;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 31: CRUD Kegiatan Pondok (F31)
 *
 * Source: dokum-cp1 §5.2 Q-ADM-02..05, Build Plan §6.3
 * Minimum 5 test methods covering:
 * - Listing kegiatan pondok
 * - Creating kegiatan pondok with target tingkatan
 * - Validation on missing required name and invalid tingkatan
 * - Viewing detail and edit forms
 * - Updating kegiatan details
 * - Deleting kegiatan pondok
 * - Role-based authorization guard (403 for non-admin)
 */
class F31_KegiatanPondokCrudTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    public function test_admin_can_view_kegiatan_index_page(): void
    {
        $admin = $this->createAdmin();
        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Tahfidz Al-Qur\'an Intensif',
            'deskripsi' => 'Program hafalan Qur\'an harian',
            'tingkatan' => 'semua',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.kegiatan.index'));

        $response->assertOk();
        $response->assertSee('Tahfidz Al-Qur\'an Intensif');
    }

    public function test_admin_can_create_new_kegiatan_pondok(): void
    {
        $admin = $this->createAdmin();

        $payload = [
            'nama_kegiatan' => 'Kajian Kitab Fathul Qorib',
            'deskripsi' => 'Kajian fiqih kitab kuning madzhab Syafi\'i',
            'tingkatan' => 'aliyah',
            'kelas' => '10',
        ];

        $response = $this->actingAs($admin)->post(route('admin.kegiatan.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.kegiatan.index'));

        $this->assertDatabaseHas('kegiatan_pondok', [
            'nama_kegiatan' => 'Kajian Kitab Fathul Qorib',
            'tingkatan' => 'aliyah',
            'kelas' => '10',
        ]);
    }

    public function test_create_kegiatan_validation_fails_on_missing_nama_kegiatan(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.kegiatan.store'), [
            'nama_kegiatan' => '',
            'tingkatan' => 'tsanawiyah',
        ]);

        $response->assertSessionHasErrors(['nama_kegiatan']);
    }

    public function test_admin_can_view_kegiatan_detail_and_edit_page(): void
    {
        $admin = $this->createAdmin();
        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Tahfidz Pagi',
            'deskripsi' => 'Bada subuh sampai syuruq',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);

        $responseShow = $this->actingAs($admin)->get(route('admin.kegiatan.show', $kegiatan->id));
        $responseShow->assertOk();
        $responseShow->assertSee('Tahfidz Pagi');

        $responseEdit = $this->actingAs($admin)->get(route('admin.kegiatan.edit', $kegiatan->id));
        $responseEdit->assertOk();
        $responseEdit->assertSee('Tahfidz Pagi');
    }

    public function test_admin_can_update_kegiatan_pondok(): void
    {
        $admin = $this->createAdmin();
        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Halaqah Tahfidz Awal',
            'deskripsi' => 'Deskripsi lama',
            'tingkatan' => 'semua',
        ]);

        $updatePayload = [
            'nama_kegiatan' => 'Halaqah Tahfidz Lanjutan',
            'deskripsi' => 'Deskripsi diperbarui',
            'tingkatan' => 'aliyah',
            'kelas' => '11',
        ];

        $response = $this->actingAs($admin)->put(route('admin.kegiatan.update', $kegiatan->id), $updatePayload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('kegiatan_pondok', [
            'id' => $kegiatan->id,
            'nama_kegiatan' => 'Halaqah Tahfidz Lanjutan',
            'tingkatan' => 'aliyah',
        ]);
    }

    public function test_admin_can_delete_kegiatan_pondok(): void
    {
        $admin = $this->createAdmin();
        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Kegiatan Sementara',
            'tingkatan' => 'semua',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.kegiatan.destroy', $kegiatan->id));

        $response->assertRedirect(route('admin.kegiatan.index'));
        $this->assertDatabaseMissing('kegiatan_pondok', ['id' => $kegiatan->id]);
    }

    public function test_unauthorized_roles_cannot_manage_kegiatan(): void
    {
        $ustadz = $this->createUstadz();
        $wali = $this->createWaliMurid();
        $calon = $this->createCalonSantri();

        $this->actingAs($ustadz)->get(route('admin.kegiatan.index'))->assertForbidden();
        $this->actingAs($wali)->get(route('admin.kegiatan.index'))->assertForbidden();
        $this->actingAs($calon)->get(route('admin.kegiatan.index'))->assertForbidden();

        $this->actingAs($ustadz)->post(route('admin.kegiatan.store'), [
            'nama_kegiatan' => 'Illegal Activity',
            'tingkatan' => 'semua',
        ])->assertForbidden();
    }
}
