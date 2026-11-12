<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\Kontak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 08: Kontak Resmi Publik (F08)
 *
 * Source: dokum-ppdb §5.1, §7.1, Build Plan §7.1
 * Verifies official institution contact info, address, phone number, and email.
 */
class F08_KontakResmiTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    public function test_guest_can_access_official_contact_page(): void
    {
        $response = $this->get(route('contact'));

        $response->assertStatus(200);
        $response->assertViewIs('public.contact');
        $response->assertViewHas('kontak');
    }

    public function test_contact_page_displays_institution_phone_and_email(): void
    {
        Kontak::create([
            'alamat' => 'Komplek Pondok Pesantren Tahfidz Ibnu Juraimi',
            'telepon' => '081122334455',
            'email' => 'info@ibnujuraimi.sch.id',
        ]);

        $response = $this->get(route('contact'));

        $response->assertStatus(200);
        $response->assertSee('081122334455');
        $response->assertSee('info@ibnujuraimi.sch.id');
    }

    public function test_contact_page_displays_institution_physical_address(): void
    {
        Kontak::create([
            'alamat' => 'Dusun Jurang, RT 02 / RW 05, Bantul, D.I. Yogyakarta 55781',
            'telepon' => '0274123456',
            'email' => 'sekretariat@ibnujuraimi.sch.id',
        ]);

        $response = $this->get(route('contact'));

        $response->assertStatus(200);
        $response->assertSee('Dusun Jurang, RT 02 / RW 05, Bantul, D.I. Yogyakarta 55781');
    }

    public function test_contact_page_handles_missing_kontak_record_without_500_error(): void
    {
        // No Kontak record created
        $response = $this->get(route('contact'));

        $response->assertStatus(200);
    }

    public function test_authenticated_users_can_view_contact_page(): void
    {
        $wali = $this->createWaliMurid();

        $response = $this->actingAs($wali)->get(route('contact'));

        $response->assertStatus(200);
        $response->assertViewIs('public.contact');
    }
}
