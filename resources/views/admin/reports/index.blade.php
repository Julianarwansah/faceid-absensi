@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Attendance Reports')

@push('styles')
    <style>
        .filter-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #666;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .report-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background: #fed7aa;
            color: #92400e;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-box {
            background: #f9fafb;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-label {
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #333;
        }
    </style>
@endpush

@section('content')
    <div class="filter-card">
        <form method="GET" action="{{ route('admin.reports.index') }}">
            <div class="form-grid">
                <div class="form-group">
                    <label>Report Type</label>
                    <select name="report_type" required>
                        <option value="">Select Type</option>
                        <option value="daily" {{ request('report_type') == 'daily' ? 'selected' : '' }}>Daily Report</option>
                        <option value="monthly" {{ request('report_type') == 'monthly' ? 'selected' : '' }}>Monthly Report
                        </option>
                        <option value="employee" {{ request('report_type') == 'employee' ? 'selected' : '' }}>Employee Report
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Date / Month</label>
                    <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}">
                </div>

                <div class="form-group">
                    <label>Department</label>
                    <select name="department_id">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Employee (for employee report)</label>
                    <select name="employee_id">
                        <option value="">Select Employee</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-chart-bar"></i> Generate Report
            </button>
        </form>
    </div>

    @if($reportData)
        <div class="report-card">
            @if($reportData['type'] == 'daily')
                <h3 style="margin-bottom: 20px;">Daily Report - {{ $reportData['date']->format('d F Y') }}</h3>

                <div class="stats-summary">
                    <div class="stat-box">
                        <div class="stat-label">Total</div>
                        <div class="stat-value">{{ $reportData['total'] }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Present</div>
                        <div class="stat-value" style="color: #10b981;">{{ $reportData['present'] }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Late</div>
                        <div class="stat-value" style="color: #f59e0b;">{{ $reportData['late'] }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Absent</div>
                        <div class="stat-value" style="color: #ef4444;">{{ $reportData['absent'] }}</div>
                    </div>
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
                        @foreach($reportData['attendances'] as $att)
                            <tr>
                                <td>{{ $att->employee->full_name }}</td>
                                <td>{{ $att->employee->department->name ?? 'N/A' }}</td>
                                <td>{{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('H:i') : '-' }}</td>
                                <td>{{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('H:i') : '-' }}</td>
                                <td>
                                    @if($att->status == 'present')
                                        <span class="badge badge-success">Present</span>
                                    @elseif($att->status == 'late')
                                        <span class="badge badge-warning">Late</span>
                                    @else
                                        <span class="badge badge-danger">Absent</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            @elseif($reportData['type'] == 'monthly')
                <h3 style="margin-bottom: 20px;">Monthly Report - {{ $reportData['month']->format('F Y') }}</h3>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Total Days</th>
                            <th>Present</th>
                            <th>Late</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData['employee_stats'] as $stat)
                            <tr>
                                <td>{{ $stat['employee']->full_name }}</td>
                                <td>{{ $stat['employee']->department->name ?? 'N/A' }}</td>
                                <td>{{ $stat['total_days'] }}</td>
                                <td><span class="badge badge-success">{{ $stat['present'] }}</span></td>
                                <td><span class="badge badge-warning">{{ $stat['late'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            @elseif($reportData['type'] == 'employee')
                <h3 style="margin-bottom: 20px;">Employee Report - {{ $reportData['employee']->full_name }}</h3>

                <div class="stats-summary">
                    <div class="stat-box">
                        <div class="stat-label">Total Days</div>
                        <div class="stat-value">{{ $reportData['total_days'] }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Present</div>
                        <div class="stat-value" style="color: #10b981;">{{ $reportData['present'] }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Late</div>
                        <div class="stat-value" style="color: #f59e0b;">{{ $reportData['late'] }}</div>
                    </div>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Work Hours</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData['attendances'] as $att)
                            <tr>
                                <td>{{ $att->date->format('d M Y') }}</td>
                                <td>{{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('H:i') : '-' }}</td>
                                <td>{{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('H:i') : '-' }}</td>
                                <td>{{ $att->work_duration }} hours</td>
                                <td>
                                    @if($att->status == 'present')
                                        <span class="badge badge-success">Present</span>
                                    @elseif($att->status == 'late')
                                        <span class="badge badge-warning">Late</span>
                                    @else
                                        <span class="badge badge-danger">Absent</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endif
@endsection