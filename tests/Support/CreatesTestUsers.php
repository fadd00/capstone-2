<?php

namespace Tests\Support;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

trait CreatesTestUsers
{
    /**
     * Create a user assigned to a specified Spatie role.
     */
    protected function createUserWithRole(string $roleName, array $attributes = []): User
    {
        // Ensure role exists in the database
        if (class_exists(Role::class)) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        $defaultName = match ($roleName) {
            'admin' => 'Admin Test User',
            'ustadz' => 'Ustadz Ahmad Fauzi',
            'wali_murid' => 'Wali Murid Bapak Budi',
            'calon_santri' => 'Calon Santri Muhammad',
            default => 'Test User',
        };

        $user = User::create(array_merge([
            'name' => $defaultName,
            'email' => $roleName.'_'.uniqid().'@ibnujuraimi.sch.id',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ], $attributes));

        if (method_exists($user, 'assignRole')) {
            $user->assignRole($roleName);
        }

        return $user;
    }

    /**
     * Create an admin user with 'admin' Spatie role.
     */
    protected function createAdmin(array $attributes = []): User
    {
        return $this->createUserWithRole('admin', $attributes);
    }

    /**
     * Create an ustadz user with 'ustadz' Spatie role.
     */
    protected function createUstadz(array $attributes = []): User
    {
        return $this->createUserWithRole('ustadz', $attributes);
    }

    /**
     * Create a wali murid user with 'wali_murid' Spatie role.
     */
    protected function createWaliMurid(array $attributes = []): User
    {
        return $this->createUserWithRole('wali_murid', $attributes);
    }

    /**
     * Create a calon santri user with 'calon_santri' Spatie role.
     */
    protected function createCalonSantri(array $attributes = []): User
    {
        return $this->createUserWithRole('calon_santri', $attributes);
    }
}
