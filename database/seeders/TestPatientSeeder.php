<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestPatientSeeder extends Seeder
{
    public function run(): void
    {
        $u = User::updateOrCreate(
            ['email' => 'cesar@test.com'],
            [
                'name' => 'Cesar Test',
                'password' => Hash::make('cesar123'),
                'email_verified_at' => now()
            ]
        );
        $u->assignRole('patient');
    }
}
