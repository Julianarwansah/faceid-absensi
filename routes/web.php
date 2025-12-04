<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\FaceRecognitionController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Face recognition routes
    Route::post('/face/register', [FaceRecognitionController::class, 'register'])->name('face.register');
    Route::post('/face/verify', [FaceRecognitionController::class, 'verify'])->name('face.verify');
    Route::get('/face/descriptor/{employeeId}', [FaceRecognitionController::class, 'getDescriptor'])->name('face.descriptor');

    // Employee routes (accessible by employees and admins)
    Route::middleware('employee')->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
        Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');
        Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
    });

    // Admin routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // Employee management
        Route::resource('employees', EmployeeController::class);
        Route::post('employees/{id}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');

        // Department management
        Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::post('departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::put('departments/{id}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('departments/{id}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
        Route::post('departments/{id}/toggle-status', [DepartmentController::class, 'toggleStatus'])->name('departments.toggle-status');

        // Reports
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

        // Settings
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
