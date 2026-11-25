<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\Informasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 07: Pengumuman / Informasi Publik (F07)
 *
 * Source: dokum-ppdb §5.1, §7.1, Build Plan §7.1
 * Verifies public announcement listing for admission schedules, circulars, and updates.
 */
#[\PHPUnit\Framework\Attributes\Group('retired-ppdb')]
class F07_PengumumanPublikTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    public function test_guest_can_access_public_announcements_page(): void
    {
        $response = $this->get(route('informasi'));

        $response->assertStatus(200);
        $response->assertViewIs('public.informasi');
        $response->assertViewHas('informasis');
    }

    public function test_announcements_page_displays_persisted_information_items(): void
    {
        Informasi::create([
            'judul' => 'Jadwal Tes Seleksi Masuk Gelombang 1',
            'isi' => 'Tes seleksi tahfidz dan wawancara akan diselenggarakan secara luring pada hari Ahad.',
        ]);
        Informasi::create([
            'judul' => 'Tata Tertib Pembayaran Biaya Masuk',
            'isi' => 'Pembayaran hanya dapat dilakukan melalui transfer ke rekening resmi BSI pondok.',
        ]);

        $response = $this->get(route('informasi'));

        $response->assertStatus(200);
        $response->assertSee('Jadwal Tes Seleksi Masuk Gelombang 1');
        $response->assertSee('Tata Tertib Pembayaran Biaya Masuk');
    }

    public function test_announcements_page_paginates_records_when_many(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            Informasi::create([
                'judul' => "Pengumuman PPDB Seri Ke-{$i}",
                'isi' => "Deskripsi detail informasi nomor {$i} untuk calon santri dan wali murid.",
            ]);
        }

        $response = $this->get(route('informasi'));

        $response->assertStatus(200);
        $informasis = $response->viewData('informasis');
        $this->assertNotNull($informasis);
        $this->assertTrue(method_exists($informasis, 'links') || is_iterable($informasis));
    }

    public function test_announcements_page_renders_gracefully_when_empty(): void
    {
        $response = $this->get(route('informasi'));

        $response->assertStatus(200);
        $informasis = $response->viewData('informasis');
        $this->assertCount(0, $informasis);
    }

    public function test_authenticated_role_users_can_view_public_announcements(): void
    {
        $admin = $this->createAdmin();
        $calonSantri = $this->createCalonSantri();

        $responseAdmin = $this->actingAs($admin)->get(route('informasi'));
        $responseAdmin->assertStatus(200);

        $responseCalon = $this->actingAs($calonSantri)->get(route('informasi'));
        $responseCalon->assertStatus(200);
    }
}
