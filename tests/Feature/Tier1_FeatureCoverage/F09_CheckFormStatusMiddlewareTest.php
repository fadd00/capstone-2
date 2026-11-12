<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 09: Form Pendaftaran & Schedule Guard (F09)
 *
 * Source: dokum-ppdb §3.1, §6.3, Build Plan §7.2
 * Verifies CheckFormStatus middleware protecting GET /pendaftaran
 * based on status ('Buka'/'Tutup') and dynamic opening/closing date windows.
 */
class F09_CheckFormStatusMiddlewareTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, RefreshDatabase;

    public function test_registration_form_accessible_when_status_is_buka_without_date_limits(): void
    {
        $this->createStatusForm('Buka', null, null);

        $response = $this->get(route('pendaftaran'));

        $response->assertStatus(200);
        $response->assertViewIs('public.pendaftaran');
    }

    public function test_registration_form_accessible_when_current_date_is_within_open_window(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        $this->createStatusForm('Buka', '2026-05-01', '2026-05-31');

        $response = $this->get(route('pendaftaran'));

        $response->assertStatus(200);

        Carbon::setTestNow();
    }

    public function test_registration_form_redirects_when_status_is_explicitly_tutup(): void
    {
        $this->createStatusForm('Tutup', null, null);

        $response = $this->get(route('pendaftaran'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
    }

    public function test_registration_form_redirects_when_current_date_is_before_tanggal_buka(): void
    {
        Carbon::setTestNow('2026-04-20 08:00:00');

        $this->createStatusForm('Buka', '2026-05-01', '2026-05-31');

        $response = $this->get(route('pendaftaran'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');

        Carbon::setTestNow();
    }

    public function test_registration_form_redirects_when_current_date_is_after_tanggal_tutup(): void
    {
        Carbon::setTestNow('2026-06-05 14:00:00');

        $this->createStatusForm('Buka', '2026-05-01', '2026-05-31');

        $response = $this->get(route('pendaftaran'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');

        Carbon::setTestNow();
    }

    public function test_registration_form_defaults_to_accessible_when_no_status_form_record_exists(): void
    {
        // No status_forms record exists in fresh database
        $response = $this->get(route('pendaftaran'));

        $response->assertStatus(200);
    }
}
