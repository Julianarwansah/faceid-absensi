@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'Attendance Settings')

@push('styles')
    <style>
        .settings-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            max-width: 800px;
            margin: 0 auto;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #666;
        }

        .form-group input,
        .form-group select {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }

        .btn {
            padding: 12px 24px;
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

        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin: 25px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
    </style>
@endpush

@section('content')
    <div class="settings-card">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="section-title">Work Hours</div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="work_start_time">Work Start Time</label>
                    <input type="time" id="work_start_time" name="work_start_time"
                        value="{{ substr($settings->work_start_time, 0, 5) }}" required>
                </div>

                <div class="form-group">
                    <label for="work_end_time">Work End Time</label>
                    <input type="time" id="work_end_time" name="work_end_time"
                        value="{{ substr($settings->work_end_time, 0, 5) }}" required>
                </div>
            </div>

            <div class="section-title">Attendance Thresholds</div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="late_threshold_minutes">Late Threshold (minutes)</label>
                    <input type="number" id="late_threshold_minutes" name="late_threshold_minutes"
                        value="{{ $settings->late_threshold_minutes }}" min="0" required>
                    <small style="color: #666; margin-top: 5px;">Minutes after work start time to mark as late</small>
                </div>

                <div class="form-group">
                    <label for="half_day_threshold_minutes">Half Day Threshold (minutes)</label>
                    <input type="number" id="half_day_threshold_minutes" name="half_day_threshold_minutes"
                        value="{{ $settings->half_day_threshold_minutes }}" min="0" required>
                    <small style="color: #666; margin-top: 5px;">Minimum work minutes for full day</small>
                </div>
            </div>

            <div class="section-title">Location Settings (Optional)</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="require_location" value="1" {{ $settings->require_location ? 'checked' : '' }}>
                        Require Location for Attendance
                    </label>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="office_latitude">Office Latitude</label>
                    <input type="number" step="0.00000001" id="office_latitude" name="office_latitude"
                        value="{{ $settings->office_latitude }}">
                </div>

                <div class="form-group">
                    <label for="office_longitude">Office Longitude</label>
                    <input type="number" step="0.00000001" id="office_longitude" name="office_longitude"
                        value="{{ $settings->office_longitude }}">
                </div>

                <div class="form-group">
                    <label for="location_radius_meters">Location Radius (meters)</label>
                    <input type="number" id="location_radius_meters" name="location_radius_meters"
                        value="{{ $settings->location_radius_meters }}" min="0">
                </div>
            </div>

            <div style="text-align: right; margin-top: 30px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
@endsection