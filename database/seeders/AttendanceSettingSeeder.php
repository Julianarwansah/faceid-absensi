<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('attendance_settings')->insert([
            'work_start_time' => '08:00:00',
            'work_end_time' => '17:00:00',
            'late_threshold_minutes' => 15,
            'half_day_threshold_minutes' => 240,
            'weekend_days' => json_encode(['Saturday', 'Sunday']),
            'require_location' => false,
            'office_latitude' => null,
            'office_longitude' => null,
            'location_radius_meters' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
