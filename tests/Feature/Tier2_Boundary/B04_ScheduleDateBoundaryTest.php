<?php

namespace Tests\Feature\Tier2_Boundary;

use App\Models\StatusForm;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesPpdbFixtures;
use Tests\TestCase;

/**
 * Tier 2 Boundary Test: B04 Schedule Date Boundary
 *
 * Source: dokum-ppdb §3.1, §6.3, §5.3 Q8, Build Plan §7.3
 * Verifies boundaries of CheckFormStatus middleware and StatusForm schedule:
 * - Exact 00:00:00 on opening date (allowed)
 * - Exact 23:59:59 on closing date (allowed)
 * - 1 second before opening date (blocked -> redirect to /)
 * - 1 second after closing date (blocked -> redirect to /)
 * - Null dates with status 'Buka' (allowed)
 * - Status 'Tutup' explicitly (blocked regardless of dates)
 */
#[\PHPUnit\Framework\Attributes\Group('retired-ppdb')]
class B04_ScheduleDateBoundaryTest extends TestCase
{
    use CreatesPpdbFixtures, RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Reset mocked time
        parent::tearDown();
    }

    /**
     * 1. Access is allowed at exactly 00:00:00 on the opening date.
     */
    public function test_access_allowed_at_exact_opening_date_start_of_day(): void
    {
        $this->createStatusForm('Buka', '2026-05-01', '2026-05-31');

        Carbon::setTestNow(Carbon::parse('2026-05-01 00:00:00'));

        $response = $this->get(route('pendaftaran'));

        $response->assertStatus(200);
        $response->assertViewIs('public.pendaftaran');
    }

    /**
     * 2. Access is allowed at exactly 23:59:59 on the closing date.
     */
    public function test_access_allowed_at_exact_closing_date_end_of_day(): void
    {
        $this->createStatusForm('Buka', '2026-05-01', '2026-05-31');

        Carbon::setTestNow(Carbon::parse('2026-05-31 23:59:59'));

        $response = $this->get(route('pendaftaran'));

        $response->assertStatus(200);
        $response->assertViewIs('public.pendaftaran');
    }

    /**
     * 3. Access is blocked 1 second before opening date (2026-04-30 23:59:59).
     */
    public function test_access_blocked_one_second_before_opening_date(): void
    {
        $this->createStatusForm('Buka', '2026-05-01', '2026-05-31');

        Carbon::setTestNow(Carbon::parse('2026-04-30 23:59:59'));

        $response = $this->get(route('pendaftaran'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
    }

    /**
     * 4. Access is blocked 1 second after closing date (2026-06-01 00:00:00).
     */
    public function test_access_blocked_one_second_after_closing_date(): void
    {
        $this->createStatusForm('Buka', '2026-05-01', '2026-05-31');

        Carbon::setTestNow(Carbon::parse('2026-06-01 00:00:00'));

        $response = $this->get(route('pendaftaran'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
    }

    /**
     * 5. Access is allowed unconditionally when status is 'Buka' with null opening and closing dates.
     */
    public function test_access_allowed_when_status_is_buka_with_null_dates(): void
    {
        $this->createStatusForm('Buka', null, null);

        $response = $this->get(route('pendaftaran'));

        $response->assertStatus(200);
        $response->assertViewIs('public.pendaftaran');
    }

    /**
     * 6. Access is blocked when status is explicitly 'Tutup' regardless of whether current time is within date range.
     */
    public function test_access_blocked_when_status_is_explicitly_tutup(): void
    {
        $this->createStatusForm('Tutup', '2026-05-01', '2026-05-31');

        Carbon::setTestNow(Carbon::parse('2026-05-15 12:00:00'));

        $response = $this->get(route('pendaftaran'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
    }
}
