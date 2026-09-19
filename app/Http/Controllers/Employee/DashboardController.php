<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\AttendanceService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(AttendanceService $service): View
    {
        $employee = $this->currentEmployee();
        $state = $service->dashboardState($employee);
        $schedule = $state['schedule'] ?? null;

        $recentHistory = $employee->attendanceRecords()
            ->with('attendanceLocation')
            ->orderByDesc('date')
            ->take(5)
            ->get();

        return view('employee.dashboard', compact('employee', 'state', 'schedule', 'recentHistory'));
    }

    private function currentEmployee(): Employee
    {
        return auth()->user()->employee;
    }
}