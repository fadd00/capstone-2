<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\Carousel;
use App\Models\Informasi;
use App\Models\Keunggulan;
use App\Models\Kontak;
use App\Models\Statistik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 25: CMS Management Admin (F25)
 *
 * Source: dokum-ppdb §5.4, Build Plan §6.4
 * Verifies admin CRUD operations across all CMS entities:
 * Carousels, Keunggulan, Statistik (live counter), Informasi (announcements), and Kontak.
 */
#[\PHPUnit\Framework\Attributes\Group('retired-ppdb')]
class F25_CmsAdminCrudTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    public function test_admin_can_crud_carousel_banners(): void
    {
        Storage::fake('public');

        $admin = $this->createAdmin();

        // 1. Create Carousel
        $image = UploadedFile::fake()->image('banner_hero.jpg', 1200, 600);
        $response = $this->actingAs($admin)->post(route('admin.carousel.store'), [
            'image' => $image,
            'caption' => 'Pendidikan Qurani Berkualitas',
            'order' => 1,
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('carousels', ['caption' => 'Pendidikan Qurani Berkualitas']);

        $carousel = Carousel::where('caption', 'Pendidikan Qurani Berkualitas')->firstOrFail();

        // 2. Update Carousel
        $updateResponse = $this->actingAs($admin)->put(route('admin.carousel.update', $carousel->id), [
            'caption' => 'Pendidikan Qurani Terakreditasi',
            'order' => 2,
        ]);
        $updateResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('carousels', ['caption' => 'Pendidikan Qurani Terakreditasi', 'order' => 2]);

        // 3. Delete Carousel
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.carousel.destroy', $carousel->id));
        $deleteResponse->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('carousels', ['id' => $carousel->id]);
    }

    public function test_admin_can_crud_keunggulan_points(): void
    {
        $admin = $this->createAdmin();

        // 1. Create
        $response = $this->actingAs($admin)->post(route('admin.keunggulan.store'), [
            'judul' => 'Asrama Representatif',
            'deskripsi_singkat' => 'Fasilitas kamar ber-AC dan nyaman',
            'deskripsi_detail' => 'Deskripsi detail tentang asrama santri...',
            'order' => 1,
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('keunggulans', ['judul' => 'Asrama Representatif']);

        $keunggulan = Keunggulan::where('judul', 'Asrama Representatif')->firstOrFail();

        // 2. Update
        $this->actingAs($admin)->put(route('admin.keunggulan.update', $keunggulan->id), [
            'judul' => 'Asrama Representatif & Modern',
            'deskripsi_singkat' => 'Fasilitas kamar ber-AC dan nyaman',
            'deskripsi_detail' => 'Deskripsi detail tentang asrama santri updated...',
            'order' => 2,
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('keunggulans', ['judul' => 'Asrama Representatif & Modern']);

        // 3. Delete
        $this->actingAs($admin)->delete(route('admin.keunggulan.destroy', $keunggulan->id))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('keunggulans', ['id' => $keunggulan->id]);
    }

    public function test_admin_can_update_live_counter_statistiks(): void
    {
        $admin = $this->createAdmin();
        Statistik::create([
            'santri_aktif' => 100,
            'asatidzah_bersanad' => 20,
            'tahun_berkhidmat' => 10,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.statistik.update'), [
            'santri_aktif' => 175,
            'asatidzah_bersanad' => 35,
            'tahun_berkhidmat' => 14,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('statistiks', [
            'santri_aktif' => 175,
            'asatidzah_bersanad' => 35,
            'tahun_berkhidmat' => 14,
        ]);
    }

    public function test_admin_can_crud_informasi_announcements(): void
    {
        $admin = $this->createAdmin();

        // 1. Create
        $response = $this->actingAs($admin)->post(route('admin.informasi.store'), [
            'judul' => 'Hasil Seleksi Gelombang II',
            'isi' => 'Daftar nama calon santri yang dinyatakan lulus seleksi tahap dua...',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('informasis', ['judul' => 'Hasil Seleksi Gelombang II']);

        $info = Informasi::where('judul', 'Hasil Seleksi Gelombang II')->firstOrFail();

        // 2. Update
        $this->actingAs($admin)->put(route('admin.informasi.update', $info->id), [
            'judul' => 'Hasil Seleksi Gelombang II (Revisi)',
            'isi' => 'Ralat pengumuman nama santri lulus...',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('informasis', ['judul' => 'Hasil Seleksi Gelombang II (Revisi)']);

        // 3. Delete
        $this->actingAs($admin)->delete(route('admin.informasi.destroy', $info->id))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('informasis', ['id' => $info->id]);
    }

    public function test_admin_can_update_institution_kontak(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->put(route('admin.kontak.update'), [
            'alamat' => 'Komplek Utama PPTQ Ibnu Juraimi Yogyakarta',
            'telepon' => '082199887766',
            'email' => 'humas@ibnujuraimi.sch.id',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('kontaks', [
            'alamat' => 'Komplek Utama PPTQ Ibnu Juraimi Yogyakarta',
            'telepon' => '082199887766',
            'email' => 'humas@ibnujuraimi.sch.id',
        ]);
    }

    public function test_non_admin_forbidden_from_cms_admin_endpoints(): void
    {
        $calonSantri = $this->createCalonSantri();

        $this->actingAs($calonSantri)->get(route('admin.carousel.index'))->assertStatus(403);
        $this->actingAs($calonSantri)->get(route('admin.keunggulan.index'))->assertStatus(403);
        $this->actingAs($calonSantri)->get(route('admin.statistik.edit'))->assertStatus(403);
        $this->actingAs($calonSantri)->get(route('admin.informasi.index'))->assertStatus(403);
        $this->actingAs($calonSantri)->get(route('admin.kontak'))->assertStatus(403);
    }
}
