<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access with all privileges',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'employee',
                'display_name' => 'Employee',
                'description' => 'Regular employee with attendance access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
