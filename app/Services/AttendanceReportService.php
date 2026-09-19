<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AttendanceReportService
{
    /**
     * Status yang dikenal sistem saat ini.
     */
    public const STATUSES = ['Hadir', 'Terlambat'];

    public function __construct(
        private readonly DistanceService $distanceService,
    ) {
    }

    /**
     * Normalisasi filter dari request agar seluruh export/print konsisten.
     *
     * @return array<string, mixed>
     */
    public function normalizeFilters(array $raw): array
    {
        return [
            'filter' => $raw['filter'] ?? 'semua',
            'date_from' => $raw['date_from'] ?? null,
            'date_to' => $raw['date_to'] ?? null,
            'search' => isset($raw['search']) ? trim((string) $raw['search']) : null,
            'employee_id' => $raw['employee_id'] ?? null,
            'status' => $raw['status'] ?? 'semua',
            'location_id' => $raw['location_id'] ?? null,
        ];
    }

    /**
     * Query utama daftar absensi (untuk tabel web).
     */
    public function recordsQuery(array $filters): Builder
    {
        $filters = $this->normalizeFilters($filters);

        $query = AttendanceRecord::with(['employee.user', 'attendanceLocation'])
            ->orderByDesc('date');

        $this->applyDateRange($query, $this->resolveDateRange($filters));

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->whereHas('employee.user', fn (Builder $u) => $u->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('employee', fn (Builder $e) => $e->where('employee_number', 'like', "%{$search}%"));
            });
        }

        if (! empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (! empty($filters['location_id'])) {
            $query->where('attendance_location_id', $filters['location_id']);
        }

        if ($this->isKnownStatus($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    /**
     * Query karyawan yang seharusnya bekerja namun tidak memiliki record
     * absensi pada rentang tanggal (status "Tidak Hadir").
     */
    public function absentEmployeesQuery(array $filters): Builder
    {
        $filters = $this->normalizeFilters($filters);
        [$from, $to] = $this->resolveDateRange($filters);

        $query = Employee::with('user')
            ->where('status', Employee::STATUS_ACTIVE);

        if ($from && $to) {
            $dayNames = $this->indonesianDayNamesInRange($from, $to);
            $query->whereHas('workSchedules', function (Builder $q) use ($dayNames) {
                $q->where('is_active', true);
                if ($dayNames) {
                    $q->whereIn('day', $dayNames);
                }
            });

            $query->whereDoesntHave('attendanceRecords', function (Builder $q) use ($from, $to) {
                $q->whereDate('date', '>=', $from)
                    ->whereDate('date', '<=', $to);
            });
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->whereHas('user', fn (Builder $u) => $u->where('name', 'like', "%{$search}%"))
                    ->orWhere('employee_number', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['employee_id'])) {
            $query->where('id', $filters['employee_id']);
        }

        if (! empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        return $query;
    }

    public function isTidakHadirFilter(array $filters): bool
    {
        return ($filters['status'] ?? 'semua') === 'tidak_hadir';
    }

    /**
     * Data terpaginasi untuk tabel web admin.
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        if ($this->isTidakHadirFilter($filters)) {
            return $this->absentEmployeesQuery($filters)->paginate($perPage)->withQueryString();
        }

        return $this->recordsQuery($filters)->paginate($perPage)->withQueryString();
    }

    /**
     * Baris laporan (tanpa paginasi) untuk CSV/Excel/PDF/Print.
     */
    public function rows(array $filters): Collection
    {
        if ($this->isTidakHadirFilter($filters)) {
            $filters = $this->normalizeFilters($filters);
            [$from, $to] = $this->resolveDateRange($filters);
            $labelDate = $from && $to && $from === $to ? Carbon::parse($from) : null;

            return $this->absentEmployeesQuery($filters)
                ->get()
                ->map(fn (Employee $employee) => $this->absentRow($employee, $labelDate));
        }

        return $this->recordsQuery($filters)
            ->get()
            ->map(fn (AttendanceRecord $record, int $i) => $this->recordRow($record, $i + 1));
    }

    /**
     * Ringkasan kehadiran harian.
     *
     * @return array<string, int>
     */
    public function dailySummary(Carbon|string $date): array
    {
        $date = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        $totalEmployees = Employee::where('status', Employee::STATUS_ACTIVE)->count();

        $counts = AttendanceRecord::whereDate('date', $date)
            ->get()
            ->groupBy('status')
            ->map->count();

        $attended = $counts->sum();
        $present = $counts->get('Hadir', 0);
        $late = $counts->get('Terlambat', 0);
        $otherStatuses = [];

        foreach ($counts as $status => $count) {
            if (! in_array($status, ['Hadir', 'Terlambat'], true)) {
                $otherStatuses[$status] = $count;
            }
        }

        return [
            'date' => $date,
            'total_employees' => $totalEmployees,
            'total_attended' => $attended,
            'present' => $present,
            'late' => $late,
            'absent' => max(0, $totalEmployees - $attended),
            'other_statuses' => $otherStatuses,
            'attendance_rate' => $totalEmployees > 0
                ? round($attended / $totalEmployees * 100, 1)
                : 0,
        ];
    }

    /**
     * Ringkasan absensi bulanan per karyawan (terpaginasi).
     */
    public function monthlyReport(int $year, int $month, ?int $employeeId = null, ?string $department = null): LengthAwarePaginator
    {
        $monthStart = Carbon::createFromFormat('Y-n-j', "$year-$month-1")->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $employeesQuery = Employee::with('user')
            ->where('status', Employee::STATUS_ACTIVE)
            ->orderBy('employee_number');

        if ($employeeId) {
            $employeesQuery->where('id', $employeeId);
        }

        if ($department && $department !== '') {
            $employeesQuery->where('department', $department);
        }

        $paginator = $employeesQuery->paginate(20)->withQueryString();

        $dayCounts = $this->dayNameCountsInMonth($monthStart, $monthEnd);
        $globalDays = WorkSchedule::where('is_active', true)->get()->pluck('day')->unique()->values();

        $recordsByEmployee = AttendanceRecord::with('attendanceLocation')
            ->whereDate('date', '>=', $monthStart->toDateString())
            ->whereDate('date', '<=', $monthEnd->toDateString())
            ->get()
            ->groupBy('employee_id');

        foreach ($paginator->items() as $employee) {
            $employeeDays = $employee->workSchedules()
                ->where('is_active', true)
                ->get()
                ->pluck('day')
                ->unique()
                ->values();

            if ($employeeDays->isEmpty()) {
                $employeeDays = $globalDays;
            }

            $workingDays = 0;
            foreach ($employeeDays as $day) {
                $workingDays += $dayCounts[$day] ?? 0;
            }

            $records = $recordsByEmployee->get($employee->id, collect());

            $attended = $records->count();
            $present = $records->where('status', 'Hadir')->count();
            $late = $records->where('status', 'Terlambat')->count();

            $employee->setAttribute('working_days', $workingDays);
            $employee->setAttribute('attended_days', $attended);
            $employee->setAttribute('present_count', $present);
            $employee->setAttribute('late_count', $late);
            $employee->setAttribute('absent_count', max(0, $workingDays - $attended));
        }

        return $paginator;
    }

    /**
     * Tren 7 hari terakhir untuk grafik.
     *
     * @return array<int, array<string, mixed>>
     */
    public function weeklyTrend(): array
    {
        $trend = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->copy()->subDays($i)->toDateString();
            $carbon = now()->copy()->subDays($i);

            $counts = AttendanceRecord::whereDate('date', $date)
                ->get()
                ->groupBy('status')
                ->map->count();

            $trend[] = [
                'date' => $date,
                'label' => (string) $carbon->locale('id')->isoFormat('ddd'),
                'hadir' => $counts->get('Hadir', 0),
                'terlambat' => $counts->get('Terlambat', 0),
            ];
        }

        return $trend;
    }

    /**
     * Label periode untuk judul laporan/export.
     */
    public function periodLabel(array $filters): string
    {
        $filters = $this->normalizeFilters($filters);
        [$from, $to] = $this->resolveDateRange($filters);

        if ($from && $to) {
            if ($from === $to) {
                return Carbon::parse($from)->isoFormat('D MMMM Y');
            }

            return Carbon::parse($from)->isoFormat('D MMMM Y').' - '.Carbon::parse($to)->isoFormat('D MMMM Y');
        }

        return 'Semua data';
    }

    public function statusOptions(): array
    {
        return ['semua', ...self::STATUSES, 'tidak_hadir'];
    }

    public function isKnownStatus(mixed $status): bool
    {
        return is_string($status) && in_array($status, self::STATUSES, true);
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function resolveDateRange(array $filters): array
    {
        $from = $filters['date_from'] ?? null;
        $to = $filters['date_to'] ?? null;

        if ($from && $to) {
            return [$from, $to];
        }

        $today = now()->toDateString();

        return match ($filters['filter']) {
            'hari_ini' => [$today, $today],
            'kemarin' => [now()->subDay()->toDateString(), now()->subDay()->toDateString()],
            'minggu_ini' => [now()->startOfWeek()->toDateString(), $today],
            'bulan_ini' => [now()->startOfMonth()->toDateString(), $today],
            default => [null, null],
        };
    }

    private function applyDateRange(Builder $query, array $range): void
    {
        [$from, $to] = $range;

        if ($from && $to) {
            $query->whereDate('date', '>=', $from)
                ->whereDate('date', '<=', $to);
        }
    }

    /**
     * @return array<string, int> nama hari Indonesia => jumlah hari dalam bulan
     */
    private function dayNameCountsInMonth(Carbon $start, Carbon $end): array
    {
        $counts = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $day = (string) $date->locale('id')->isoFormat('dddd');
            $counts[$day] = ($counts[$day] ?? 0) + 1;
        }

        return $counts;
    }

    private function indonesianDayNamesInRange(string $from, string $to): array
    {
        $names = [];
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->startOfDay();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $names[] = (string) $date->locale('id')->isoFormat('dddd');
        }

        return array_values(array_unique($names));
    }

    /**
     * @return array<string, mixed>
     */
    private function recordRow(AttendanceRecord $record, int $index): array
    {
        $location = $record->attendanceLocation;
        $distance = $this->distanceService->haversine(
            $location?->latitude,
            $location?->longitude,
            $record->check_in_longitude !== null ? $record->check_in_latitude : null,
            $record->check_in_longitude !== null ? $record->check_in_longitude : null,
        );

        if ($distance === null && $record->check_out_longitude !== null) {
            $distance = $this->distanceService->haversine(
                $location?->latitude,
                $location?->longitude,
                $record->check_out_latitude,
                $record->check_out_longitude,
            );
        }

        return [
            'no' => $index,
            'employee_name' => $record->employee?->user?->name ?? '—',
            'employee_number' => $record->employee?->employee_number ?? '—',
            'department' => $record->employee?->department ?? '—',
            'date' => $record->date instanceof Carbon ? $record->date->toDateString() : (string) $record->date,
            'check_in' => $record->check_in ?? '',
            'check_out' => $record->check_out ?? '',
            'location' => $location?->name ?? '—',
            'status' => $record->status ?? '—',
            'check_in_latitude' => $record->check_in_latitude,
            'check_in_longitude' => $record->check_in_longitude,
            'check_out_latitude' => $record->check_out_latitude,
            'check_out_longitude' => $record->check_out_longitude,
            'distance' => $distance,
            'notes' => $record->notes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function absentRow(Employee $employee, ?Carbon $date): array
    {
        return [
            'no' => null,
            'employee_name' => $employee->user?->name ?? '—',
            'employee_number' => $employee->employee_number,
            'department' => $employee->department ?? '—',
            'date' => $date?->toDateString() ?? null,
            'check_in' => '',
            'check_out' => '',
            'location' => '—',
            'status' => 'Tidak Hadir',
            'check_in_latitude' => null,
            'check_in_longitude' => null,
            'check_out_latitude' => null,
            'check_out_longitude' => null,
            'distance' => null,
            'notes' => null,
        ];
    }
}