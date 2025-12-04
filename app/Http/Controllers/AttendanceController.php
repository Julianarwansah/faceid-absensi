<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    /**
     * Display attendance interface.
     */
    public function index()
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'Employee record not found.');
        }

        $todayAttendance = $employee->todayAttendance();
        $settings = AttendanceSetting::getInstance();

        return view('attendance.index', compact('employee', 'todayAttendance', 'settings'));
    }

    /**
     * Process check-in.
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'face_match' => 'required|boolean',
            'photo' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if (!$request->face_match) {
            return response()->json([
                'success' => false,
                'message' => 'Face verification failed. Please try again.'
            ], 400);
        }

        $employee = auth()->user()->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found.'
            ], 404);
        }

        // Check if already checked in today
        $todayAttendance = $employee->todayAttendance();
        if ($todayAttendance && $todayAttendance->check_in) {
            return response()->json([
                'success' => false,
                'message' => 'You have already checked in today.'
            ], 400);
        }

        $now = Carbon::now();
        $settings = AttendanceSetting::getInstance();

        // Determine status based on time
        $workStartTime = Carbon::parse($settings->work_start_time);
        $lateThreshold = $workStartTime->copy()->addMinutes($settings->late_threshold_minutes);

        $status = 'present';
        if ($now->greaterThan($lateThreshold)) {
            $status = 'late';
        }

        // Save photo
        $photoData = $request->photo;
        $photoData = str_replace('data:image/png;base64,', '', $photoData);
        $photoData = str_replace(' ', '+', $photoData);
        $photoName = 'checkin_' . $employee->id . '_' . date('Ymd_His') . '.png';
        Storage::disk('public')->put('attendance/' . $photoName, base64_decode($photoData));

        // Create or update attendance
        $attendance = Attendance::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => $now->toDateString(),
            ],
            [
                'check_in' => $now->toTimeString(),
                'check_in_photo' => 'attendance/' . $photoName,
                'check_in_latitude' => $request->latitude,
                'check_in_longitude' => $request->longitude,
                'status' => $status,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Check-in successful!',
            'data' => [
                'check_in' => $now->format('H:i'),
                'status' => $status,
            ]
        ]);
    }

    /**
     * Process check-out.
     */
    public function checkOut(Request $request)
    {
        $request->validate([
            'face_match' => 'required|boolean',
            'photo' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if (!$request->face_match) {
            return response()->json([
                'success' => false,
                'message' => 'Face verification failed. Please try again.'
            ], 400);
        }

        $employee = auth()->user()->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found.'
            ], 404);
        }

        // Check if checked in today
        $todayAttendance = $employee->todayAttendance();
        if (!$todayAttendance || !$todayAttendance->check_in) {
            return response()->json([
                'success' => false,
                'message' => 'You must check in first.'
            ], 400);
        }

        if ($todayAttendance->check_out) {
            return response()->json([
                'success' => false,
                'message' => 'You have already checked out today.'
            ], 400);
        }

        $now = Carbon::now();

        // Save photo
        $photoData = $request->photo;
        $photoData = str_replace('data:image/png;base64,', '', $photoData);
        $photoData = str_replace(' ', '+', $photoData);
        $photoName = 'checkout_' . $employee->id . '_' . date('Ymd_His') . '.png';
        Storage::disk('public')->put('attendance/' . $photoName, base64_decode($photoData));

        // Update attendance
        $todayAttendance->update([
            'check_out' => $now->toTimeString(),
            'check_out_photo' => 'attendance/' . $photoName,
            'check_out_latitude' => $request->latitude,
            'check_out_longitude' => $request->longitude,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-out successful!',
            'data' => [
                'check_out' => $now->format('H:i'),
            ]
        ]);
    }

    /**
     * Display attendance history.
     */
    public function history(Request $request)
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'Employee record not found.');
        }

        $query = $employee->attendances()->orderBy('date', 'desc');

        // Filter by date range if provided
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        $attendances = $query->paginate(20);

        return view('attendance.history', compact('attendances', 'employee'));
    }
}
