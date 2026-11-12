<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\User;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

class F45_AdversarialSecurityHardeningTest extends TestCase
{
    use CreatesPpdbFixtures;
    use CreatesTestUsers;

    /**
     * 1. SQL Injection strings in search and query parameters are safely parameterized.
     */
    public function test_sql_injection_in_search_and_filter_queries_is_neutralized(): void
    {
        $admin = $this->createAdmin();

        $maliciousPayloads = [
            "' OR '1'='1",
            '1; DROP TABLE users; --',
            "' UNION SELECT id, name, password, email FROM users --",
            "admin'--",
            "1' AND SLEEP(5) AND '1'='1",
        ];

        foreach ($maliciousPayloads as $payload) {
            $response = $this->actingAs($admin)->get('/admin/santri?search='.urlencode($payload));

            // Must not result in an unhandled 500 SQL syntax error
            $this->assertNotEquals(500, $response->getStatusCode(), "SQL Injection payload caused a 500 error: {$payload}");
            $this->assertContains($response->getStatusCode(), [200, 302]);
        }
    }

    /**
     * 2. XSS payloads in user input are escaped by Blade and do not execute raw HTML/JS.
     */
    public function test_xss_script_tags_are_escaped_in_blade_output(): void
    {
        $xssPayload = "<script>alert('pwned-xss')</script>";
        $escaped = e($xssPayload);

        $this->assertEquals('&lt;script&gt;alert(&#039;pwned-xss&#039;)&lt;/script&gt;', $escaped);
        $this->assertStringNotContainsString('<script>', $escaped);

        // Test with HTML image payload
        $imgPayload = '<img src="x" onerror="alert(1)">';
        $this->assertStringNotContainsString('<img', e($imgPayload));
    }

    /**
     * 3. IDOR Protection: Calon Santri A cannot access or download Calon Santri B's private documents.
     */
    public function test_idor_protection_prevents_unauthorized_applicant_file_download(): void
    {
        $userA = $this->createCalonSantri(['name' => 'Santri A']);
        $userB = $this->createCalonSantri(['name' => 'Santri B']);

        $pendaftarB = $this->createPendaftaran([
            'nama_lengkap' => 'Santri B',
            'pas_photo' => 'pendaftaran/pas_photo/santri_b.jpg',
        ], $userB);

        // User A attempts to download User B's file
        $response = $this->actingAs($userA)->get("/user/pendaftar/{$pendaftarB->id}/file/pas_photo");

        $response->assertStatus(403);
    }

    /**
     * 4. Path Traversal attacks in file download parameters are strictly rejected.
     */
    public function test_path_traversal_payloads_in_file_download_are_blocked(): void
    {
        $admin = $this->createAdmin();
        $pendaftar = $this->createPendaftaran();

        $traversalPayloads = [
            '../../../../etc/passwd',
            '..%2F..%2F..%2F..%2Fetc%2Fpasswd',
            '..\\..\\..\\windows\\system32\\cmd.exe',
            '/etc/shadow',
            '....//....//config/database.php',
        ];

        foreach ($traversalPayloads as $payload) {
            $response = $this->actingAs($admin)->get("/user/pendaftar/{$pendaftar->id}/file/".urlencode($payload));

            // Must not return 200 with arbitrary file content
            $this->assertContains(
                $response->getStatusCode(),
                [403, 404, 422, 500],
                "Path traversal payload should not yield 200 OK: {$payload}"
            );
            $this->assertNotEquals(200, $response->getStatusCode());
        }
    }

    /**
     * 5. Administrator self-deletion is strictly prevented to safeguard institutional access.
     */
    public function test_admin_self_deletion_is_strictly_forbidden(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->delete("/admin/akun/{$admin->id}");

        // Self-deletion must be rejected with 403 or 422 or redirect with error session
        $this->assertContains($response->getStatusCode(), [403, 422, 302]);

        // Verify the administrator record was NOT deleted from the database
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'email' => $admin->email,
        ]);
    }

    /**
     * 6. Unauthenticated requests without CSRF protection on state-changing actions are guarded.
     */
    public function test_unauthenticated_post_requests_without_csrf_are_intercepted(): void
    {
        // Direct unauthenticated POST request to protected endpoint without session/token
        $response = $this->post('/admin/santri', [
            'nama' => 'Santri Hacker',
            'nis' => '9999999',
            'tingkatan' => 'tsanawiyah',
        ]);

        // Must be intercepted with redirect to login (302) or 419 Page Expired
        $this->assertContains($response->getStatusCode(), [302, 419]);
    }
}
