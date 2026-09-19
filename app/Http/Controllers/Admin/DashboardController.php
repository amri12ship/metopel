<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLocation;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Services\AttendanceReportService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AttendanceReportService $reportService,
    ) {
    }

    public function index(): View
    {
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', Employee::STATUS_ACTIVE)->count();
        $totalLocations = AttendanceLocation::count();
        $activeLocations = AttendanceLocation::where('is_active', true)->count();

        $today = now()->toDateString();
        $presentToday = AttendanceRecord::whereDate('date', $today)->where('status', 'Hadir')->count();
        $lateToday = AttendanceRecord::whereDate('date', $today)->where('status', 'Terlambat')->count();
        $attendedToday = AttendanceRecord::whereDate('date', $today)->count();
        $absentToday = max(0, $activeEmployees - $attendedToday);
        $attendanceRate = $activeEmployees > 0
            ? round($attendedToday / $activeEmployees * 100, 1)
            : 0;

        $weeklyTrend = $this->reportService->weeklyTrend();

        $recentEmployees = Employee::with('user')->latest()->take(5)->get();
        $activeLocationsList = AttendanceLocation::where('is_active', true)
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalEmployees',
            'activeEmployees',
            'totalLocations',
            'activeLocations',
            'presentToday',
            'lateToday',
            'absentToday',
            'attendanceRate',
            'weeklyTrend',
            'recentEmployees',
            'activeLocationsList',
        ));
    }
}