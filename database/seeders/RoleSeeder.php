<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

                // ✅ Create roles
                Role::firstOrCreate(['name' => 'admin']);
                Role::firstOrCreate(['name' => 'user']);
                Role::firstOrCreate(['name' => 'manager']);

                $this->command->info('✅ Roles seeded successfully!');
    }
}
