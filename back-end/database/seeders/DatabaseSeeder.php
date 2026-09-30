<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


        \App\Models\User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ])->assignRole('admin');

        \App\Models\User::factory()->create([
            'name' => 'testUser',
            'email' => 'testuser@gmail.com',
            'password' => bcrypt('password'),
        ])->assignRole('user');

        \App\Models\User::factory()->create([
            'name' => 'testCommercial',
            'email' => 'testcommercial@gmail.com',
            'password' => bcrypt('password'),
        ])->assignRole('commercial');



        $this->call([RolePermissionSeeder::class, CompanySettingSeeder::class]);
    }
}
