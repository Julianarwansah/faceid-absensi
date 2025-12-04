<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display dashboard.
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        } else {
            return $this->employeeDashboard();
        }
    }

    /**
     * Admin dashboard view.
     */
    private function adminDashboard()
    {
        $today = Carbon::today();

        // Get today's statistics
        $totalEmployees = Employee::active()->count();
        $todayAttendances = Attendance::byDate($today)->count();
        $presentToday = Attendance::byDate($today)->where('status', 'present')->count();
        $lateToday = Attendance::byDate($today)->where('status', 'late')->count();
        $absentToday = $totalEmployees - $todayAttendances;

        // Get recent attendances
        $recentAttendances = Attendance::with(['employee.department'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get weekly attendance data for chart
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $weeklyData[] = [
                'date' => $date->format('D'),
                'present' => Attendance::byDate($date)->where('status', 'present')->count(),
                'late' => Attendance::byDate($date)->where('status', 'late')->count(),
                'absent' => $totalEmployees - Attendance::byDate($date)->count(),
            ];
        }

        return view('dashboard', compact(
            'totalEmployees',
            'todayAttendances',
            'presentToday',
            'lateToday',
            'absentToday',
            'recentAttendances',
            'weeklyData'
        ));
    }

    /**
     * Employee dashboard view.
     */
    private function employeeDashboard()
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return redirect()->route('login')->with('error', 'Employee record not found.');
        }

        $todayAttendance = $employee->todayAttendance();

        // Get this month's attendance
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $monthlyAttendances = $employee->attendances()
            ->byDateRange($monthStart, $monthEnd)
            ->orderBy('date', 'desc')
            ->get();

        $presentDays = $monthlyAttendances->where('status', 'present')->count();
        $lateDays = $monthlyAttendances->where('status', 'late')->count();
        $totalDays = $monthlyAttendances->count();

        return view('dashboard', compact(
            'employee',
            'todayAttendance',
            'monthlyAttendances',
            'presentDays',
            'lateDays',
            'totalDays'
        ));
    }
}
