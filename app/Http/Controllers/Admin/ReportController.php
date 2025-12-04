<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Department;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display report interface.
     */
    public function index(Request $request)
    {
        $departments = Department::active()->get();
        $employees = Employee::active()->get();

        $reportData = null;

        if ($request->filled('report_type')) {
            switch ($request->report_type) {
                case 'daily':
                    $reportData = $this->dailyReport($request);
                    break;
                case 'monthly':
                    $reportData = $this->monthlyReport($request);
                    break;
                case 'employee':
                    $reportData = $this->employeeReport($request);
                    break;
            }
        }

        return view('admin.reports.index', compact('departments', 'employees', 'reportData'));
    }

    /**
     * Generate daily report.
     */
    private function dailyReport($request)
    {
        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();

        $query = Attendance::with(['employee.department'])
            ->byDate($date);

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        $attendances = $query->get();

        return [
            'type' => 'daily',
            'date' => $date,
            'attendances' => $attendances,
            'total' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'absent' => Employee::active()->count() - $attendances->count(),
        ];
    }

    /**
     * Generate monthly report.
     */
    private function monthlyReport($request)
    {
        $month = $request->filled('month') ? Carbon::parse($request->month) : Carbon::now();
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();

        $query = Attendance::with(['employee.department'])
            ->byDateRange($startDate, $endDate);

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        $attendances = $query->get();

        // Group by employee
        $employeeStats = [];
        foreach ($attendances->groupBy('employee_id') as $employeeId => $empAttendances) {
            $employee = $empAttendances->first()->employee;
            $employeeStats[] = [
                'employee' => $employee,
                'total_days' => $empAttendances->count(),
                'present' => $empAttendances->where('status', 'present')->count(),
                'late' => $empAttendances->where('status', 'late')->count(),
            ];
        }

        return [
            'type' => 'monthly',
            'month' => $month,
            'employee_stats' => $employeeStats,
        ];
    }

    /**
     * Generate employee-specific report.
     */
    private function employeeReport($request)
    {
        if (!$request->filled('employee_id')) {
            return null;
        }

        $employee = Employee::findOrFail($request->employee_id);
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        $attendances = $employee->attendances()
            ->byDateRange($startDate, $endDate)
            ->orderBy('date', 'desc')
            ->get();

        return [
            'type' => 'employee',
            'employee' => $employee,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'attendances' => $attendances,
            'total_days' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
        ];
    }
}
