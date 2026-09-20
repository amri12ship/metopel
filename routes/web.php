<?php

use App\Http\Controllers\AbsensiScanController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\AttendanceLocationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\WorkScheduleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Employee\AttendanceController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\ProfileController as EmployeeProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/absensi/scan/{public_token}', [AbsensiScanController::class, 'show'])
    ->name('absensi.scan');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::patch('/employees/{employee}/toggle', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    Route::get('/locations', [AttendanceLocationController::class, 'index'])->name('locations.index');
    Route::get('/locations/create', [AttendanceLocationController::class, 'create'])->name('locations.create');
    Route::post('/locations', [AttendanceLocationController::class, 'store'])->name('locations.store');
    Route::get('/locations/{location}', [AttendanceLocationController::class, 'show'])->name('locations.show');
    Route::get('/locations/{location}/edit', [AttendanceLocationController::class, 'edit'])->name('locations.edit');
    Route::put('/locations/{location}', [AttendanceLocationController::class, 'update'])->name('locations.update');
    Route::patch('/locations/{location}/toggle', [AttendanceLocationController::class, 'toggleStatus'])->name('locations.toggle');
    Route::get('/locations/{location}/qr', [AttendanceLocationController::class, 'qr'])->name('locations.qr');
    Route::post('/locations/{location}/generate-qr', [AttendanceLocationController::class, 'generateQr'])->name('locations.generate-qr');

    Route::get('/schedules', [WorkScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/create', [WorkScheduleController::class, 'create'])->name('schedules.create');
    Route::post('/schedules', [WorkScheduleController::class, 'store'])->name('schedules.store');
    Route::get('/schedules/{schedule}/edit', [WorkScheduleController::class, 'edit'])->name('schedules.edit');
    Route::put('/schedules/{schedule}', [WorkScheduleController::class, 'update'])->name('schedules.update');
    Route::patch('/schedules/{schedule}/toggle', [WorkScheduleController::class, 'toggleStatus'])->name('schedules.toggle');
    Route::delete('/schedules/{schedule}', [WorkScheduleController::class, 'destroy'])->name('schedules.destroy');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/attendance', [AdminAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{record}', [AdminAttendanceController::class, 'show'])->name('attendance.show');

    Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
    Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
    Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');
    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
});

Route::middleware(['auth', 'employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');
    Route::get('/attendance/{record}', [AttendanceController::class, 'show'])->name('attendance.show');

    Route::get('/profile', [EmployeeProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [EmployeeProfileController::class, 'update'])->name('profile.update');
});