<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\Murojaah;
use App\Models\Santri;
use App\Models\SetoranHafalan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 41: Dashboard Wali Murid (F41)
 *
 * Source: dokum-cp1 §5.4 Q-WAL-01, Build Plan §10.1
 * Minimum 5 test methods covering:
 * - Viewing wali murid dashboard
 * - Displaying enrolled children and summary statistics (setoran/murojaah)
 * - Strict multi-tenant isolation (cannot see other parents' children)
 * - Displaying multiple children under the same guardian
 * - Graceful handling when no children are currently linked
 * - Role-based authorization guard (403 for non-wali)
 */
class F41_WaliMuridDashboardTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    public function test_wali_murid_can_access_dashboard(): void
    {
        $wali = $this->createWaliMurid();

        $response = $this->actingAs($wali)->get(route('wali-murid.dashboard'));

        $response->assertOk();
    }

    public function test_dashboard_displays_wali_children_and_hafalan_progress(): void
    {
        $wali = $this->createWaliMurid(['name' => 'Bapak Sulaiman']);
        $ustadz = $this->createUstadz();

        $santri = Santri::create([
            'nama' => 'Muhammad Hafizh',
            'nis' => '2026090',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
            'wali_murid_id' => $wali->id,
        ]);

        SetoranHafalan::create([
            'santri_id' => $santri->id,
            'ustadz_id' => $ustadz->id,
            'tanggal' => '2026-11-12',
            'juz' => 30,
            'surat' => 'An-Naba',
            'ayat' => '1-40',
            'nilai' => 95.00,
            'status_selesai' => 1,
        ]);

        $response = $this->actingAs($wali)->get(route('wali-murid.dashboard'));

        $response->assertOk();
        $response->assertSee('Muhammad Hafizh');
        $response->assertSee('An-Naba');
    }

    public function test_strict_isolation_prevents_viewing_other_parents_children(): void
    {
        $waliSelf = $this->createWaliMurid(['name' => 'Wali Pertama']);
        $waliOther = $this->createWaliMurid(['name' => 'Wali Kedua']);

        $santriSelf = Santri::create([
            'nama' => 'Anak Kandungku',
            'nis' => '2026091',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
            'wali_murid_id' => $waliSelf->id,
        ]);

        $santriOther = Santri::create([
            'nama' => 'Anak Orang Lain',
            'nis' => '2026092',
            'tingkatan' => 'aliyah',
            'kelas' => '10',
            'wali_murid_id' => $waliOther->id,
        ]);

        $response = $this->actingAs($waliSelf)->get(route('wali-murid.dashboard'));

        $response->assertOk();
        $response->assertSee('Anak Kandungku');
        $response->assertDontSee('Anak Orang Lain');
    }

    public function test_dashboard_displays_multiple_children_under_same_guardian(): void
    {
        $wali = $this->createWaliMurid();

        $anak1 = Santri::create([
            'nama' => 'Anak Pertama',
            'nis' => '2026093',
            'tingkatan' => 'tsanawiyah',
            'kelas' => '7',
            'wali_murid_id' => $wali->id,
        ]);

        $anak2 = Santri::create([
            'nama' => 'Anak Kedua',
            'nis' => '2026094',
            'tingkatan' => 'aliyah',
            'kelas' => '10',
            'wali_murid_id' => $wali->id,
        ]);

        $response = $this->actingAs($wali)->get(route('wali-murid.dashboard'));

        $response->assertOk();
        $response->assertSee('Anak Pertama');
        $response->assertSee('Anak Kedua');
    }

    public function test_dashboard_handles_empty_children_list_gracefully(): void
    {
        $waliTanpaAnak = $this->createWaliMurid();

        $response = $this->actingAs($waliTanpaAnak)->get(route('wali-murid.dashboard'));

        $response->assertOk();
    }

    public function test_non_wali_roles_cannot_access_wali_dashboard(): void
    {
        $ustadz = $this->createUstadz();
        $calon = $this->createCalonSantri();

        $this->actingAs($ustadz)->get(route('wali-murid.dashboard'))->assertForbidden();
        $this->actingAs($calon)->get(route('wali-murid.dashboard'))->assertForbidden();
    }
}
