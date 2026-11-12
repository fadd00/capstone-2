<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Primary Admin PPDB (dokum-ppdb.md §4.3)
        $adminPpdb = User::updateOrCreate(
            ['email' => 'Muhammadiyah@gmail.com'],
            [
                'name' => 'Administrator Utama',
                'password' => Hash::make('DiHati*2000#'),
                'email_verified_at' => now(),
            ]
        );
        $adminPpdb->syncRoles(['admin']);

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
