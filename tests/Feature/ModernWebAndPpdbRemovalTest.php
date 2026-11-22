<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

class ModernWebAndPpdbRemovalTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    public function test_ppdb_cms_broadcast_and_self_register_routes_are_removed(): void
    {
        $admin = $this->createAdmin();

        foreach ([
            '/pendaftaran',
            '/informasi',
            '/contact',
            '/register',
            '/register-success',
            '/user/dashboard',
        ] as $publicOrRemovedPath) {
            $this->get($publicOrRemovedPath)->assertNotFound();
        }

        foreach ([
            '/admin/ppdb',
            '/admin/notifikasi',
            '/admin/status-form',
            '/admin/carousel',
            '/admin/keunggulan',
            '/admin/statistik',
            '/admin/informasi',
            '/admin/kontak',
        ] as $adminRemovedPath) {
            $this->actingAs($admin)->get($adminRemovedPath)->assertNotFound();
        }
    }

    public function test_home_page_is_login_only_with_modern_web_semantics_and_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
        $response->assertHeader('Cross-Origin-Resource-Policy', 'same-origin');
        $this->assertStringContainsString("frame-ancestors 'none'", (string) $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString('geolocation=(self)', (string) $response->headers->get('Permissions-Policy'));

        $response->assertSee('<html lang="id">', false);
        $response->assertSee('class="skip-link"', false);
        $response->assertSee('id="main-content"', false);
        $response->assertSee('data-password-toggle="password"', false);
        $response->assertDontSee('Daftar PPDB');
        $response->assertDontSee('Pengumuman');
    }

    public function test_authenticated_portals_use_native_dialog_popover_and_accessible_tables(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz();
        $wali = $this->createWaliMurid();

        $adminDash = $this->actingAs($admin)->get(route('admin.dashboard'));
        $adminDash->assertOk();
        $adminDash->assertSee('<h1', false);
        $adminDash->assertSee('aria-current="page"', false);
        $adminDash->assertSee('id="app-confirm-dialog"', false);
        $adminDash->assertSee('popover="auto"', false);
        $adminDash->assertDontSee('Kelola PPDB');
        $adminDash->assertDontSee('Broadcast WhatsApp');
        $adminDash->assertDontSee('onclick=');
        $adminDash->assertDontSee('onsubmit=');

        $santriIndex = $this->actingAs($admin)->get(route('admin.santri.index'));
        $santriIndex->assertOk();
        $santriIndex->assertSee('<search', false);
        $santriIndex->assertSee('role="region"', false);
        $santriIndex->assertSee('scope="col"', false);

        $ustadzCreate = $this->actingAs($ustadz)->get(route('ustadz.presensi-kegiatan.create'));
        $ustadzCreate->assertOk();
        $ustadzCreate->assertSee('data-geo-trigger', false);
        $ustadzCreate->assertSee('type="time"', false);
        $ustadzCreate->assertDontSee('onclick=');
        $ustadzCreate->assertDontSee('alert(');

        $waliDash = $this->actingAs($wali)->get(route('wali-murid.dashboard'));
        $waliDash->assertOk();
        $waliDash->assertSee('<h1', false);
        $waliDash->assertSee('Laporan Mingguan Tahfidz');
        $waliDash->assertSee('Riwayat Kehadiran Santri');
    }

    public function test_logout_sets_clear_site_data_header(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('logout'));

        $response->assertRedirect('/');
        $response->assertHeader('Clear-Site-Data', '"cookies", "storage", "cache"');
    }
}
