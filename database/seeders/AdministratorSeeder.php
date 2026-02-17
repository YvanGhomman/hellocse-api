<?php

namespace Database\Seeders;

use App\Models\Administrator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdministratorSeeder extends Seeder
{
    public function run(): void
    {
        // Admin de test
        Administrator::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);

        // Autres admins aléatoires
        Administrator::factory(5)->create();
    }
}