<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\Santri;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 33: Penugasan Santri Binaan (F33)
 *
 * Source: dokum-cp1 §5.2 Q-ADM-10, Build Plan §6.3
 * Minimum 5 test methods covering:
 * - Viewing assignment index
 * - Assigning multiple santri to ustadz pembina
 * - Syncing / replacing existing binaan santri
 * - Clearing all binaan santri
 * - Validation on invalid santri IDs
 * - Role-based authorization guard (403 for non-admin)
 */
class F33_PenugasanSantriBinaanTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    public function test_admin_can_view_santri_binaan_index_page(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz(['name' => 'Ustadz Hasan Al-Basri']);

        $response = $this->actingAs($admin)->get(route('admin.santri-binaan.index'));

        $response->assertOk();
        $response->assertSee('Ustadz Hasan Al-Basri');
    }

    public function test_admin_can_assign_santri_binaan_to_ustadz(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz();

        $santri1 = Santri::create([
            'nama' => 'Muhammad Hafizh',
            'nis' => '2026030',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);
        $santri2 = Santri::create([
            'nama' => 'Ahmad Fauzi',
            'nis' => '2026031',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
        ]);

        $payload = [
            'santri_ids' => [$santri1->id, $santri2->id],
        ];

        $response = $this->actingAs($admin)->put(route('admin.santri-binaan.update', $ustadz->id), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('ustadz_santri', [
            'user_id' => $ustadz->id,
            'santri_id' => $santri1->id,
        ]);
        $this->assertDatabaseHas('ustadz_santri', [
            'user_id' => $ustadz->id,
            'santri_id' => $santri2->id,
        ]);

        $this->assertCount(2, $ustadz->fresh()->santriBinaan);
    }

    public function test_admin_can_sync_and_replace_assigned_santri_binaan(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz();

        $santriOld = Santri::create(['nama' => 'Santri Lama', 'nis' => '2026032', 'tingkatan' => 'tsanawiyah']);
        $santriNew = Santri::create(['nama' => 'Santri Baru', 'nis' => '2026033', 'tingkatan' => 'tsanawiyah']);

        $ustadz->santriBinaan()->attach($santriOld->id);

        $payload = [
            'santri_ids' => [$santriNew->id],
        ];

        $response = $this->actingAs($admin)->put(route('admin.santri-binaan.update', $ustadz->id), $payload);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('ustadz_santri', [
            'user_id' => $ustadz->id,
            'santri_id' => $santriOld->id,
        ]);
        $this->assertDatabaseHas('ustadz_santri', [
            'user_id' => $ustadz->id,
            'santri_id' => $santriNew->id,
        ]);
    }

    public function test_admin_can_clear_all_santri_binaan(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz();

        $santri = Santri::create(['nama' => 'Santri Binaan', 'nis' => '2026034', 'tingkatan' => 'tsanawiyah']);
        $ustadz->santriBinaan()->attach($santri->id);

        $payload = [
            'santri_ids' => [],
        ];

        $response = $this->actingAs($admin)->put(route('admin.santri-binaan.update', $ustadz->id), $payload);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('ustadz_santri', ['user_id' => $ustadz->id]);
        $this->assertCount(0, $ustadz->fresh()->santriBinaan);
    }

    public function test_assigning_invalid_santri_id_fails_validation(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz();

        $response = $this->actingAs($admin)->put(route('admin.santri-binaan.update', $ustadz->id), [
            'santri_ids' => [99999],
        ]);

        $response->assertSessionHasErrors(['santri_ids.0']);
    }

    public function test_non_admin_cannot_manage_santri_binaan(): void
    {
        $ustadz = $this->createUstadz();
        $wali = $this->createWaliMurid();
        $calon = $this->createCalonSantri();

        $this->actingAs($ustadz)->get(route('admin.santri-binaan.index'))->assertForbidden();
        $this->actingAs($wali)->get(route('admin.santri-binaan.index'))->assertForbidden();
        $this->actingAs($calon)->get(route('admin.santri-binaan.index'))->assertForbidden();

        $this->actingAs($ustadz)->put(route('admin.santri-binaan.update', $ustadz->id), [
            'santri_ids' => [1],
        ])->assertForbidden();
    }
}
