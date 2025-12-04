<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin role ID
        $adminRoleId = DB::table('roles')->where('name', 'admin')->first()->id;

        // Create admin user
        $userId = DB::table('users')->insertGetId([
            'name' => 'Administrator',
            'email' => 'admin@absensifaceid.com',
            'password' => Hash::make('admin123'),
            'role_id' => $adminRoleId,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create employee record for admin
        DB::table('employees')->insert([
            'user_id' => $userId,
            'department_id' => 1, // IT Department
            'employee_id' => 'EMP001',
            'full_name' => 'Administrator',
            'email' => 'admin@absensifaceid.com',
            'phone' => '081234567890',
            'position' => 'System Administrator',
            'join_date' => now(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
