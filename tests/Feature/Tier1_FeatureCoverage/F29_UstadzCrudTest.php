<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Feature 29: CRUD Ustadz (F29)
 *
 * Source: dokum-cp1 §5.2 Q-ADM-08, Build Plan §6.3
 * Minimum 5 test methods covering:
 * - Listing ustadz accounts
 * - Creating ustadz with role assignment
 * - Validation on duplicate email and invalid credentials
 * - Updating ustadz details
 * - Deleting ustadz account
 * - Role-based authorization guard (403 for unauthorized roles)
 */
class F29_UstadzCrudTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    public function test_admin_can_view_ustadz_index_page(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz(['name' => 'Ustadz Abdullah Faqih']);

        $response = $this->actingAs($admin)->get(route('admin.ustadz.index'));

        $response->assertOk();
        $response->assertSee('Ustadz Abdullah Faqih');
    }

    public function test_admin_can_create_new_ustadz_account(): void
    {
        $admin = $this->createAdmin();

        $payload = [
            'name' => 'Ustadz Muhammad Ridwan',
            'email' => 'ridwan@pptq.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($admin)->post(route('admin.ustadz.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.ustadz.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Ustadz Muhammad Ridwan',
            'email' => 'ridwan@pptq.test',
        ]);

        $ustadz = User::where('email', 'ridwan@pptq.test')->firstOrFail();
        $this->assertTrue($ustadz->hasRole('ustadz'));
        $this->assertTrue(Hash::check('password123', $ustadz->password));
    }

    public function test_create_ustadz_validation_fails_on_duplicate_email_or_invalid_password(): void
    {
        $admin = $this->createAdmin();
        $this->createUstadz(['email' => 'existing_ustadz@pptq.test']);

        // Duplicate email
        $responseDuplicate = $this->actingAs($admin)->post(route('admin.ustadz.store'), [
            'name' => 'Duplicate Ustadz',
            'email' => 'existing_ustadz@pptq.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $responseDuplicate->assertSessionHasErrors(['email']);

        // Password mismatch
        $responseMismatch = $this->actingAs($admin)->post(route('admin.ustadz.store'), [
            'name' => 'Mismatch Ustadz',
            'email' => 'mismatch@pptq.test',
            'password' => 'password123',
            'password_confirmation' => 'different_pass',
        ]);
        $responseMismatch->assertSessionHasErrors(['password']);
    }

    public function test_admin_can_view_ustadz_profile_and_edit_page(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz(['name' => 'Ustadz Hasan Al-Basri']);

        $responseShow = $this->actingAs($admin)->get(route('admin.ustadz.show', $ustadz->id));
        $responseShow->assertOk();
        $responseShow->assertSee('Ustadz Hasan Al-Basri');

        $responseEdit = $this->actingAs($admin)->get(route('admin.ustadz.edit', $ustadz->id));
        $responseEdit->assertOk();
        $responseEdit->assertSee('Ustadz Hasan Al-Basri');
    }

    public function test_admin_can_update_ustadz_information(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz([
            'name' => 'Ustadz Imam Syafii',
            'email' => 'syafii@pptq.test',
        ]);

        $updatePayload = [
            'name' => 'Ustadz Dr. Imam Syafi\'i Al-Hafizh',
            'email' => 'syafii_updated@pptq.test',
        ];

        $response = $this->actingAs($admin)->put(route('admin.ustadz.update', $ustadz->id), $updatePayload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $ustadz->id,
            'name' => 'Ustadz Dr. Imam Syafi\'i Al-Hafizh',
            'email' => 'syafii_updated@pptq.test',
        ]);
    }

    public function test_admin_can_delete_ustadz(): void
    {
        $admin = $this->createAdmin();
        $ustadz = $this->createUstadz(['email' => 'ustadz_to_delete@pptq.test']);

        $response = $this->actingAs($admin)->delete(route('admin.ustadz.destroy', $ustadz->id));

        $response->assertRedirect(route('admin.ustadz.index'));
        $this->assertDatabaseMissing('users', ['id' => $ustadz->id]);
    }

    public function test_non_admin_cannot_access_ustadz_management(): void
    {
        $ustadz = $this->createUstadz();
        $wali = $this->createWaliMurid();
        $calon = $this->createCalonSantri();

        $this->actingAs($ustadz)->get(route('admin.ustadz.index'))->assertForbidden();
        $this->actingAs($wali)->get(route('admin.ustadz.index'))->assertForbidden();
        $this->actingAs($calon)->get(route('admin.ustadz.index'))->assertForbidden();

        $this->actingAs($ustadz)->post(route('admin.ustadz.store'), [
            'name' => 'Hacker Ustadz',
            'email' => 'hacker@pptq.test',
            'password' => 'secret123',
        ])->assertForbidden();
    }
}
