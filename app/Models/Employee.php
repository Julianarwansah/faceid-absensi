<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'employee_id',
        'full_name',
        'email',
        'phone',
        'position',
        'address',
        'join_date',
        'photo',
        'face_descriptor',
        'is_active',
    ];

    protected $casts = [
        'face_descriptor' => 'array',
        'join_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the employee.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department that owns the employee.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the attendances for the employee.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Scope a query to only include active employees.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if employee has registered face.
     */
    public function hasFaceRegistered()
    {
        return !is_null($this->face_descriptor);
    }

    /**
     * Get today's attendance.
     */
    public function todayAttendance()
    {
        return $this->attendances()->whereDate('date', today())->first();
    }
}
