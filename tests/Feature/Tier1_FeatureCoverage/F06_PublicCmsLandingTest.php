<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\Carousel;
use App\Models\Keunggulan;
use App\Models\Kontak;
use App\Models\Statistik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 06: Public Landing Page CMS (F06)
 *
 * Source: dokum-ppdb §5.1, §7.1, Build Plan §7.1
 * Verifies public homepage dynamic slider carousel, keunggulan cards,
 * live institution statistics counter, and official contact information.
 */
#[\PHPUnit\Framework\Attributes\Group('retired-ppdb')]
class F06_PublicCmsLandingTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    public function test_guest_can_access_landing_page_successfully(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewIs('public.home');
        $response->assertViewHasAll(['carousels', 'keunggulans', 'statistik', 'kontak']);
    }

    public function test_landing_page_displays_carousels_in_ascending_order(): void
    {
        Carousel::create([
            'image' => 'carousel/slide2.jpg',
            'caption' => 'Slide Dua - Pendidikan Karakter',
            'order' => 2,
        ]);
        Carousel::create([
            'image' => 'carousel/slide1.jpg',
            'caption' => 'Slide Satu - Tahfidz Al-Quran',
            'order' => 1,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Slide Satu - Tahfidz Al-Quran');
        $response->assertSee('Slide Dua - Pendidikan Karakter');

        $carousels = $response->viewData('carousels');
        $this->assertCount(2, $carousels);
        $this->assertEquals(1, $carousels->first()->order);
        $this->assertEquals(2, $carousels->last()->order);
    }

    public function test_landing_page_displays_keunggulan_features(): void
    {
        Keunggulan::create([
            'judul' => 'Kurikulum Bersanad',
            'deskripsi_singkat' => 'Sanad muttasil hingga Rasulullah SAW',
            'deskripsi_detail' => 'Penjelasan lengkap mengenai sanad tahfidz institusi...',
            'order' => 1,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Kurikulum Bersanad');
        $response->assertSee('Sanad muttasil hingga Rasulullah SAW');
    }

    public function test_landing_page_displays_live_statistics_data(): void
    {
        Statistik::create([
            'santri_aktif' => 250,
            'asatidzah_bersanad' => 45,
            'tahun_berkhidmat' => 15,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $statistik = $response->viewData('statistik');
        $this->assertNotNull($statistik);
        $this->assertEquals(250, $statistik->santri_aktif);
        $this->assertEquals(45, $statistik->asatidzah_bersanad);
        $this->assertEquals(15, $statistik->tahun_berkhidmat);
    }

    public function test_landing_page_displays_institution_contact_details(): void
    {
        Kontak::create([
            'alamat' => 'Jl. Imogiri Timur KM 10, Bantul, Yogyakarta',
            'telepon' => '081234567890',
            'email' => 'sekretariat@ibnujuraimi.sch.id',
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Jl. Imogiri Timur KM 10, Bantul, Yogyakarta');
        $response->assertSee('081234567890');
        $response->assertSee('sekretariat@ibnujuraimi.sch.id');
    }

    public function test_authenticated_users_can_visit_landing_page_without_redirection(): void
    {
        $calonSantri = $this->createCalonSantri();

        $response = $this->actingAs($calonSantri)->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewIs('public.home');
    }
}
