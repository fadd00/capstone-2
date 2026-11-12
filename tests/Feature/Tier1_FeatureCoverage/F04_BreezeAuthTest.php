<?php

namespace Tests\Feature\Tier1_FeatureCoverage;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

class F04_BreezeAuthTest extends TestCase
{
    use CreatesTestUsers;

    /**
     * 1. Login screen renders successfully.
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * 2. Users can authenticate using valid credentials.
     */
    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::create([
            'name' => 'Ahmad User',
            'email' => 'ahmad@ibnujuraimi.sch.id',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'ahmad@ibnujuraimi.sch.id',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertStatus(302);
    }

    /**
     * 3. Users cannot authenticate with an invalid password.
     */
    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::create([
            'name' => 'Salma User',
            'email' => 'salma@ibnujuraimi.sch.id',
            'password' => Hash::make('password123'),
        ]);

        $this->post('/login', [
            'email' => 'salma@ibnujuraimi.sch.id',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    /**
     * 4. Authenticated users can log out.
     */
    public function test_users_can_logout(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertStatus(302);
    }

    /**
     * 5. Password reset request screen can be rendered.
     */
    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    /**
     * 6. Profile screen can be rendered for authenticated users.
     */
    public function test_profile_screen_can_be_rendered_for_authenticated_users(): void
    {
        $user = $this->createAdmin();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
    }

    /**
     * 7. User profile information can be updated via PATCH /profile.
     */
    public function test_user_profile_information_can_be_updated(): void
    {
        $user = $this->createAdmin([
            'name' => 'Original Name',
            'email' => 'original@ibnujuraimi.sch.id',
        ]);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Updated Admin Name',
            'email' => 'updated_admin@ibnujuraimi.sch.id',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $user->refresh();
        $this->assertSame('Updated Admin Name', $user->name);
        $this->assertSame('updated_admin@ibnujuraimi.sch.id', $user->email);
    }
}
