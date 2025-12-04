<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_start_time',
        'work_end_time',
        'late_threshold_minutes',
        'half_day_threshold_minutes',
        'weekend_days',
        'require_location',
        'office_latitude',
        'office_longitude',
        'location_radius_meters',
    ];

    protected $casts = [
        'weekend_days' => 'array',
        'require_location' => 'boolean',
        'office_latitude' => 'decimal:8',
        'office_longitude' => 'decimal:8',
    ];

    /**
     * Get the singleton instance.
     */
    public static function getInstance()
    {
        return self::first() ?? self::create([]);
    }
}
