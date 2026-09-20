<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLocation;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Services\AttendanceReportService;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AttendanceReportService $reportService,
    ) {
    }

    public function index(): View
    {
        $payload = Cache::remember('admin.dashboard.'.now()->toDateString(), 120, function () {
            return [
                'recentEmployees' => Employee::with('user')->latest()->take(5)->get(),
                'activeLocationsList' => AttendanceLocation::where('is_active', true)
                    ->latest()
                    ->take(5)
                    ->get(),
                ...$this->stats(),
            ];
        });

        return view('admin.dashboard', $payload);
    }

    private function stats(): array
    {
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', Employee::STATUS_ACTIVE)->count();
        $totalLocations = AttendanceLocation::count();
        $activeLocations = AttendanceLocation::where('is_active', true)->count();

        $today = now()->toDateString();
        $statuses = AttendanceRecord::whereDate('date', $today)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $attendedToday = $statuses->sum();
        $presentToday = $statuses->get('Hadir', 0);
        $lateToday = $statuses->get('Terlambat', 0);

        return [
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'totalLocations' => $totalLocations,
            'activeLocations' => $activeLocations,
            'presentToday' => $presentToday,
            'lateToday' => $lateToday,
            'attendedToday' => $attendedToday,
            'absentToday' => max(0, $activeEmployees - $attendedToday),
            'attendanceRate' => $activeEmployees > 0
                ? round($attendedToday / $activeEmployees * 100, 1)
                : 0,
            'weeklyTrend' => $this->reportService->weeklyTrend(),
        ];
    }
}