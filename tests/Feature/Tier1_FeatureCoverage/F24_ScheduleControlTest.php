<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 24: Kontrol Jadwal Form PPDB (F24)
 *
 * Source: dokum-ppdb §5.3 Q8, Build Plan §6.4, §7.2
 * Verifies admin control over PPDB schedule settings (status: Buka/Tutup,
 * tanggal_buka, tanggal_tutup) in status_forms table with validation and RBAC.
 */
#[\PHPUnit\Framework\Attributes\Group('retired-ppdb')]
class F24_ScheduleControlTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, RefreshDatabase;

    public function test_admin_can_view_schedule_control_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.statusForm'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.status-form');
        $response->assertViewHas('statusForm');
    }

    public function test_admin_can_update_registration_status_to_tutup(): void
    {
        $admin = $this->createAdmin();
        $this->createStatusForm('Buka');

        $response = $this->actingAs($admin)->post(route('admin.updateStatusForm'), [
            'status' => 'Tutup',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('status_forms', [
            'status' => 'Tutup',
        ]);
    }

    public function test_admin_can_set_dynamic_opening_and_closing_dates(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.updateStatusForm'), [
            'status' => 'Buka',
            'tanggal_buka' => '2026-05-01',
            'tanggal_tutup' => '2026-06-30',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('status_forms', [
            'status' => 'Buka',
            'tanggal_buka' => '2026-05-01',
            'tanggal_tutup' => '2026-06-30',
        ]);
    }

    public function test_schedule_update_validates_dates_correctly(): void
    {
        $admin = $this->createAdmin();

        // Closing date is before opening date (invalid)
        $response = $this->actingAs($admin)->post(route('admin.updateStatusForm'), [
            'status' => 'Buka',
            'tanggal_buka' => '2026-06-30',
            'tanggal_tutup' => '2026-05-01',
        ]);

        $response->assertSessionHasErrors('tanggal_tutup');
    }

    public function test_non_admin_cannot_view_or_update_schedule_control(): void
    {
        $calonSantri = $this->createCalonSantri();

        $this->actingAs($calonSantri)->get(route('admin.statusForm'))->assertStatus(403);

        $this->actingAs($calonSantri)->post(route('admin.updateStatusForm'), [
            'status' => 'Tutup',
        ])->assertStatus(403);
    }
}
