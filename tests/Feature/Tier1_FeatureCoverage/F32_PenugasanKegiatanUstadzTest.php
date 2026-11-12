<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\KegiatanPondok;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 32: Penugasan Kegiatan Ustadz (F32)
 *
 * Source: dokum-cp1 §5.2 Q-ADM-09, Build Plan §6.3
 * Minimum 5 test methods covering:
 * - Viewing assignment index
 * - Assigning multiple activities to ustadz
 * - Syncing / replacing existing assignments
 * - Removing all assignments
 * - Validation on non-existent activity IDs
 * - Role-based authorization guard (403 for non-admin)
 */
class F32_PenugasanKegiatanUstadzTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    public function test_admin_can_view_penugasan_kegiatan_index_page(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz(['name' => 'Ustadz Abdullah Faqih']);

        $response = $this->actingAs($admin)->get(route('admin.penugasan-kegiatan.index'));

        $response->assertOk();
        $response->assertSee('Ustadz Abdullah Faqih');
    }

    public function test_admin_can_assign_activities_to_ustadz(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz(['name' => 'Ustadz Ridwan']);

        $kegiatan1 = KegiatanPondok::create([
            'nama_kegiatan' => 'Tahfidz Pagi',
            'tingkatan' => 'tsanawiyah',
        ]);
        $kegiatan2 = KegiatanPondok::create([
            'nama_kegiatan' => 'Kajian Hadits Arbain',
            'tingkatan' => 'semua',
        ]);

        $payload = [
            'kegiatan_ids' => [$kegiatan1->id, $kegiatan2->id],
        ];

        $response = $this->actingAs($admin)->put(route('admin.penugasan-kegiatan.update', $ustadz->id), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('ustadz_kegiatan', [
            'user_id' => $ustadz->id,
            'kegiatan_pondok_id' => $kegiatan1->id,
        ]);
        $this->assertDatabaseHas('ustadz_kegiatan', [
            'user_id' => $ustadz->id,
            'kegiatan_pondok_id' => $kegiatan2->id,
        ]);

        $this->assertCount(2, $ustadz->fresh()->kegiatanPondok);
    }

    public function test_admin_can_sync_and_replace_assigned_activities(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz();

        $kegiatanOld = KegiatanPondok::create(['nama_kegiatan' => 'Kegiatan Lama', 'tingkatan' => 'semua']);
        $kegiatanNew = KegiatanPondok::create(['nama_kegiatan' => 'Kegiatan Baru', 'tingkatan' => 'semua']);

        $ustadz->kegiatanPondok()->attach($kegiatanOld->id);

        $payload = [
            'kegiatan_ids' => [$kegiatanNew->id],
        ];

        $response = $this->actingAs($admin)->put(route('admin.penugasan-kegiatan.update', $ustadz->id), $payload);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('ustadz_kegiatan', [
            'user_id' => $ustadz->id,
            'kegiatan_pondok_id' => $kegiatanOld->id,
        ]);
        $this->assertDatabaseHas('ustadz_kegiatan', [
            'user_id' => $ustadz->id,
            'kegiatan_pondok_id' => $kegiatanNew->id,
        ]);
    }

    public function test_admin_can_clear_all_assigned_activities(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz();

        $kegiatan = KegiatanPondok::create(['nama_kegiatan' => 'Kegiatan Rutin', 'tingkatan' => 'semua']);
        $ustadz->kegiatanPondok()->attach($kegiatan->id);

        $payload = [
            'kegiatan_ids' => [],
        ];

        $response = $this->actingAs($admin)->put(route('admin.penugasan-kegiatan.update', $ustadz->id), $payload);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('ustadz_kegiatan', ['user_id' => $ustadz->id]);
        $this->assertCount(0, $ustadz->fresh()->kegiatanPondok);
    }

    public function test_assigning_invalid_kegiatan_id_fails_validation(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz();

        $response = $this->actingAs($admin)->put(route('admin.penugasan-kegiatan.update', $ustadz->id), [
            'kegiatan_ids' => [99999],
        ]);

        $response->assertSessionHasErrors(['kegiatan_ids.0']);
    }

    public function test_non_admin_cannot_manage_kegiatan_assignments(): void
    {
        $ustadz = $this->createUstadz();
        $wali = $this->createWaliMurid();
        $calon = $this->createCalonSantri();

        $this->actingAs($ustadz)->get(route('admin.penugasan-kegiatan.index'))->assertForbidden();
        $this->actingAs($wali)->get(route('admin.penugasan-kegiatan.index'))->assertForbidden();
        $this->actingAs($calon)->get(route('admin.penugasan-kegiatan.index'))->assertForbidden();

        $this->actingAs($ustadz)->put(route('admin.penugasan-kegiatan.update', $ustadz->id), [
            'kegiatan_ids' => [1],
        ])->assertForbidden();
    }
}
