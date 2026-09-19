<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLocation;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Services\AttendanceReportService;
use App\Services\DistanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceReportService $reportService,
    ) {
    }

    public function index(Request $request): View
    {
        $filters = $this->reportService->normalizeFilters($request->all());
        $isAbsent = $this->reportService->isTidakHadirFilter($filters);

        $pagination = $this->reportService->paginate($filters, 15);
        $records = $pagination;

        $stats = [
            'hadir_hari_ini' => AttendanceRecord::whereDate('date', now()->toDateString())
                ->where('status', 'Hadir')
                ->count(),
            'terlambat_hari_ini' => AttendanceRecord::whereDate('date', now()->toDateString())
                ->where('status', 'Terlambat')
                ->count(),
            'total_hari_ini' => AttendanceRecord::whereDate('date', now()->toDateString())->count(),
            'total_karyawan_aktif' => Employee::where('status', Employee::STATUS_ACTIVE)->count(),
        ];

        $employees = Employee::with('user')
            ->orderBy('employee_number')
            ->get();

        $locations = AttendanceLocation::orderBy('name')->get();

        $total = $pagination->total();
        $from = $pagination->firstItem() ?? 0;
        $to = $pagination->lastItem() ?? 0;

        return view('admin.attendance.index', compact(
            'records',
            'filters',
            'isAbsent',
            'stats',
            'employees',
            'locations',
            'total',
            'from',
            'to',
        ));
    }

    public function show(
        AttendanceRecord $record,
        DistanceService $distanceService,
    ): View {
        $record->load(['employee.user', 'attendanceLocation']);

        $distance = $distanceService->haversine(
            $record->attendanceLocation?->latitude,
            $record->attendanceLocation?->longitude,
            $record->check_in_latitude,
            $record->check_in_longitude,
        );

        if ($distance === null && $record->check_out_latitude !== null) {
            $distance = $distanceService->haversine(
                $record->attendanceLocation?->latitude,
                $record->attendanceLocation?->longitude,
                $record->check_out_latitude,
                $record->check_out_longitude,
            );
        }

        return view('admin.attendance.show', compact('record', 'distance'));
    }
}