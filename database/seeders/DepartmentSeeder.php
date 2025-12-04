<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'Information Technology', 'code' => 'IT', 'description' => 'IT Department'],
            ['name' => 'Human Resources', 'code' => 'HR', 'description' => 'HR Department'],
            ['name' => 'Finance', 'code' => 'FIN', 'description' => 'Finance Department'],
            ['name' => 'Operations', 'code' => 'OPS', 'description' => 'Operations Department'],
            ['name' => 'Marketing', 'code' => 'MKT', 'description' => 'Marketing Department'],
        ];

        foreach ($departments as $department) {
            DB::table('departments')->insert([
                'name' => $department['name'],
                'code' => $department['code'],
                'description' => $department['description'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
