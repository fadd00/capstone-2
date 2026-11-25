<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin utama dari .env (ADMIN_EMAIL / ADMIN_PASSWORD), tanpa kredensial di repo.
        //    firstOrCreate: menjalankan ulang seeder tidak mereset password yang sudah diganti.
        $email = config('app.admin_email');
        $password = config('app.admin_password');
        if ($email && $password) {
            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'Administrator Utama',
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                ]
            )->syncRoles(['admin']);
        }

        // 2. Admin Pesantren (Strict Build Plan & SCOPE.md)
        $adminPesantren = User::updateOrCreate(
            ['email' => 'admin@ibnujuraimi.sch.id'],
            [
                'name' => 'Admin Pesantren',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $adminPesantren->syncRoles(['admin']);

        // 3. Admin Academic (dokum-cp1.md §9.3)
        $adminTest = User::updateOrCreate(
            ['email' => 'admin@pptq.test'],
            [
                'name' => 'Administrator PPTQ',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $adminTest->syncRoles(['admin']);
    }
}
