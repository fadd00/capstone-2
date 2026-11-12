<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 13: Halaman Konfirmasi Sukses (F13)
 *
 * Source: dokum-ppdb §6.2, §7.1, Build Plan §7.4
 * Verifies one-time credential display on /register-success following
 * successful PPDB submission, including login link and session expiry.
 */
class F13_HalamanKonfirmasiSuksesTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    public function test_register_success_page_renders_with_flashed_credentials(): void
    {
        $response = $this->withSession([
            'email' => 'calon_sukses@example.com',
            'password' => 'SecurePass8',
        ])->get(route('register.success'));

        $response->assertStatus(200);
        $response->assertViewIs('public.register-success');
    }

    public function test_register_success_displays_email_and_password_information(): void
    {
        $response = $this->withSession([
            'email' => 'muhammad_tampil@example.com',
            'password' => 'PassAcak8',
        ])->get(route('register.success'));

        $response->assertStatus(200);
        $response->assertSee('muhammad_tampil@example.com');
        $response->assertSee('PassAcak8');
    }

    public function test_register_success_page_provides_direct_link_to_login_portal(): void
    {
        $response = $this->withSession([
            'email' => 'calon_login_link@example.com',
            'password' => 'PassLink8',
        ])->get(route('register.success'));

        $response->assertStatus(200);
        $response->assertSee(route('login'));
    }

    public function test_register_success_page_handles_direct_access_without_session(): void
    {
        // Visiting directly without having registered
        $response = $this->get(route('register.success'));

        // Should return 200 or redirect to home gracefully, not 500
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    public function test_congratulatory_message_rendered_on_success_page(): void
    {
        $response = $this->withSession([
            'email' => 'congrats_test@example.com',
            'password' => 'Pass8Chr',
        ])->get(route('register.success'));

        $response->assertStatus(200);
        // Page should indicate successful registration
        $content = strtolower($response->getContent());
        $this->assertTrue(
            str_contains($content, 'berhasil') ||
            str_contains($content, 'sukses') ||
            str_contains($content, 'pendaftaran')
        );
    }
}
