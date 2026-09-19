<?php

namespace App\Http\Controllers\Employee;

use App\Exceptions\AttendanceException;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(AttendanceService $service): View
    {
        $employee = $this->currentEmployee();
        $state = $service->dashboardState($employee);
        $schedule = $state['schedule'] ?? null;
        $today = now()->toDateString();
        $todayNice = now()->isoFormat('dddd, D MMMM Y');

        $mode = $state['status'] === 'hadir' ? 'pulang' : 'masuk';
        $submitRoute = $mode === 'pulang'
            ? route('employee.attendance.check-out')
            : route('employee.attendance.check-in');

        return view('employee.attendance.index', compact(
            'employee',
            'state',
            'schedule',
            'today',
            'todayNice',
            'mode',
            'submitRoute',
        ));
    }

    public function checkIn(Request $request, AttendanceService $service): JsonResponse
    {
        try {
            $payload = $this->validatePayload($request);
            $employee = $this->currentEmployee();

            $record = $service->checkIn(
                $employee,
                $payload['token'],
                (float) $payload['latitude'],
                (float) $payload['longitude'],
            );

            return response()->json([
                'success' => true,
                'message' => 'Check-in berhasil ('.$record->status.').',
                'redirect' => route('employee.dashboard'),
                'record' => $this->recordPayload($record),
            ]);
        } catch (ValidationException $e) {
            return $this->failResponse($e->getMessage());
        } catch (AttendanceException $e) {
            return $this->failResponse($e->getMessage());
        }
    }

    public function checkOut(Request $request, AttendanceService $service): JsonResponse
    {
        try {
            $payload = $this->validatePayload($request);
            $employee = $this->currentEmployee();

            $record = $service->checkOut(
                $employee,
                $payload['token'],
                (float) $payload['latitude'],
                (float) $payload['longitude'],
            );

            return response()->json([
                'success' => true,
                'message' => 'Check-out berhasil.',
                'redirect' => route('employee.dashboard'),
                'record' => $this->recordPayload($record),
            ]);
        } catch (ValidationException $e) {
            return $this->failResponse($e->getMessage());
        } catch (AttendanceException $e) {
            return $this->failResponse($e->getMessage());
        }
    }

    public function history(Request $request): View
    {
        $employee = $this->currentEmployee();

        $query = $employee->attendanceRecords()
            ->with('attendanceLocation')
            ->orderByDesc('date');

        $filter = $request->string('filter', 'all')->toString();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $month = $request->input('month');
        $status = $request->input('status');

        if ($filter === 'hari_ini') {
            $query->whereDate('date', now()->toDateString());
        } elseif ($filter === 'minggu_ini') {
            $startOfWeek = now()->startOfWeek()->toDateString();
            $query->whereBetween('date', [$startOfWeek, now()->toDateString()]);
        } elseif ($filter === 'bulan_ini') {
            $startOfMonth = now()->startOfMonth()->toDateString();
            $query->whereBetween('date', [$startOfMonth, now()->toDateString()]);
        } elseif ($month) {
            try {
                $startOfMonth = Carbon::parse($month.'-01')->startOfMonth()->toDateString();
                $endOfMonth = Carbon::parse($month.'-01')->endOfMonth()->toDateString();
                $query->whereBetween('date', [$startOfMonth, $endOfMonth]);
            } catch (\Throwable) {
                // abaikan filter bulan tidak valid
            }
        } elseif ($dateFrom && $dateTo) {
            $query->whereBetween('date', [$dateFrom, $dateTo]);
        }

        if ($status && in_array($status, ['Hadir', 'Terlambat'], true)) {
            $query->where('status', $status);
        }

        $allowRange = $request->boolean('show_range');

        $records = $query->paginate(10)->withQueryString();

        return view('employee.attendance.history', compact(
            'employee',
            'records',
            'filter',
            'dateFrom',
            'dateTo',
            'month',
            'status',
            'allowRange',
        ));
    }

    public function show(AttendanceRecord $record): View
    {
        $employee = $this->currentEmployee();

        if ($record->employee_id !== $employee->id) {
            abort(404);
        }

        $record->load(['attendanceLocation', 'employee.user']);

        return view('employee.attendance.show', compact('record', 'employee'));
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'token' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ], [
            'token.required' => 'Kode QR belum ditemukan.',
            'latitude.required' => 'Lokasi GPS tidak ditemukan. Izinkan akses lokasi.',
            'longitude.required' => 'Lokasi GPS tidak ditemukan. Izinkan akses lokasi.',
        ]);
    }

    private function currentEmployee(): Employee
    {
        return auth()->user()->employee;
    }

    private function recordPayload(AttendanceRecord $record): array
    {
        return [
            'id' => $record->id,
            'date' => $record->date->format('Y-m-d'),
            'check_in' => $record->check_in,
            'check_out' => $record->check_out,
            'status' => $record->status,
            'location' => $record->attendanceLocation->name,
        ];
    }

    private function failResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 422);
    }
}