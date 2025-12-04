@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .stat-icon.blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
    .stat-icon.green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }
    .stat-icon.yellow { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; }
    .stat-icon.red { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; }

    .stat-label {
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #333;
    }

    .card {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
    }

    .card-header {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th {
        text-align: left;
        padding: 12px;
        background: #f9fafb;
        font-weight: 600;
        font-size: 14px;
        color: #666;
    }

    .table td {
        padding: 12px;
        border-bottom: 1px solid #f0f0f0;
    }

    .badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-warning { background: #fed7aa; color: #92400e; }
    .badge-danger { background: #fee2e2; color: #991b1b; }

    .btn {
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
</style>

@if(auth()->user()->isAdmin())
    <!-- Admin Dashboard -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Total Employees</div>
                    <div class="stat-value">{{ $totalEmployees }}</div>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Present Today</div>
                    <div class="stat-value">{{ $presentToday }}</div>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Late Today</div>
                    <div class="stat-value">{{ $lateToday }}</div>
                </div>
                <div class="stat-icon yellow">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Absent Today</div>
                    <div class="stat-value">{{ $absentToday }}</div>
                </div>
                <div class="stat-icon red">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-history"></i> Recent Attendance
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentAttendances as $attendance)
                    <tr>
                        <td>{{ $attendance->employee->full_name }}</td>
                        <td>{{ $attendance->employee->department->name ?? 'N/A' }}</td>
                        <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-' }}</td>
                        <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-' }}</td>
                        <td>
                            @if($attendance->status == 'present')
                                <span class="badge badge-success">Present</span>
                            @elseif($attendance->status == 'late')
                                <span class="badge badge-warning">Late</span>
                            @else
                                <span class="badge badge-danger">Absent</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #999;">No attendance records yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@else
    <!-- Employee Dashboard -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-label">This Month</div>
                    <div class="stat-value">{{ $totalDays }}</div>
                    <div class="stat-label">Total Days</div>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-calendar"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Present Days</div>
                    <div class="stat-value">{{ $presentDays }}</div>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Late Days</div>
                    <div class="stat-value">{{ $lateDays }}</div>
                </div>
                <div class="stat-icon yellow">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Today's Status</div>
                    <div class="stat-value" style="font-size: 20px;">
                        @if($todayAttendance)
                            @if($todayAttendance->check_in && $todayAttendance->check_out)
                                <span class="badge badge-success">Completed</span>
                            @elseif($todayAttendance->check_in)
                                <span class="badge badge-warning">Checked In</span>
                            @endif
                        @else
                            <span class="badge badge-danger">Not Marked</span>
                        @endif
                    </div>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-camera"></i> Quick Actions
        </div>
        <div style="text-align: center; padding: 20px;">
            @if(!$employee->hasFaceRegistered())
                <p style="color: #ef4444; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle"></i> You need to register your face first before marking attendance.
                </p>
                <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Register Face
                </a>
            @else
                <a href="{{ route('attendance.index') }}" class="btn btn-primary">
                    <i class="fas fa-camera"></i> Mark Attendance
                </a>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-history"></i> Recent Attendance
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monthlyAttendances->take(10) as $attendance)
                    <tr>
                        <td>{{ $attendance->date->format('d M Y') }}</td>
                        <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-' }}</td>
                        <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-' }}</td>
                        <td>
                            @if($attendance->status == 'present')
                                <span class="badge badge-success">Present</span>
                            @elseif($attendance->status == 'late')
                                <span class="badge badge-warning">Late</span>
                            @else
                                <span class="badge badge-danger">Absent</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #999;">No attendance records yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif

@endsection
