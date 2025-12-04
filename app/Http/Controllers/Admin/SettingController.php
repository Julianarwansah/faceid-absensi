<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceSetting;

class SettingController extends Controller
{
    /**
     * Display settings form.
     */
    public function index()
    {
        $settings = AttendanceSetting::getInstance();
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'work_start_time' => 'required|date_format:H:i',
            'work_end_time' => 'required|date_format:H:i',
            'late_threshold_minutes' => 'required|integer|min:0',
            'half_day_threshold_minutes' => 'required|integer|min:0',
            'weekend_days' => 'nullable|array',
            'require_location' => 'boolean',
            'office_latitude' => 'nullable|numeric',
            'office_longitude' => 'nullable|numeric',
            'location_radius_meters' => 'nullable|integer|min:0',
        ]);

        $settings = AttendanceSetting::getInstance();

        $settings->update([
            'work_start_time' => $request->work_start_time . ':00',
            'work_end_time' => $request->work_end_time . ':00',
            'late_threshold_minutes' => $request->late_threshold_minutes,
            'half_day_threshold_minutes' => $request->half_day_threshold_minutes,
            'weekend_days' => $request->weekend_days ?? [],
            'require_location' => $request->has('require_location'),
            'office_latitude' => $request->office_latitude,
            'office_longitude' => $request->office_longitude,
            'location_radius_meters' => $request->location_radius_meters ?? 100,
        ]);

        return back()->with('success', 'Settings updated successfully!');
    }
}
