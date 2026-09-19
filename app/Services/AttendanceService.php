<?php

namespace App\Services;

use App\Exceptions\AttendanceException;
use App\Models\AttendanceLocation;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public const STATUS_HADIR = 'Hadir';

    public const STATUS_TERLAMBAT = 'Terlambat';

    public function __construct(
        private readonly DistanceService $distance,
    ) {
    }

    /**
     * Proses check-in karyawan.
     */
    public function checkIn(Employee $employee, string $publicToken, float $latitude, float $longitude): AttendanceRecord
    {
        return DB::transaction(function () use ($employee, $publicToken, $latitude, $longitude) {
            $this->validateEmployee($employee);
            $location = $this->resolveLocation($publicToken);
            $this->validateCoordinates($latitude, $longitude);
            $this->validateDistance($location, $latitude, $longitude);

            $now = now();
            $today = $now->toDateString();
            $schedule = $this->resolveSchedule($employee, $today);

            $existing = AttendanceRecord::where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->first();

            if ($existing) {
                throw new AttendanceException('Anda sudah melakukan check-in hari ini.');
            }

            try {
                $record = AttendanceRecord::create([
                    'employee_id' => $employee->id,
                    'attendance_location_id' => $location->id,
                    'date' => $today,
                    'check_in' => $now->format('H:i:s'),
                    'check_in_latitude' => $latitude,
                    'check_in_longitude' => $longitude,
                    'status' => $this->determineStatus($schedule, $now),
                ]);
            } catch (UniqueConstraintViolationException) {
                throw new AttendanceException('Anda sudah melakukan check-in hari ini.');
            }

            $record->setRelation('attendanceLocation', $location);

            return $record;
        });
    }

    /**
     * Proses check-out karyawan.
     */
    public function checkOut(Employee $employee, string $publicToken, float $latitude, float $longitude): AttendanceRecord
    {
        return DB::transaction(function () use ($employee, $publicToken, $latitude, $longitude) {
            $this->validateEmployee($employee);
            $location = $this->resolveLocation($publicToken);
            $this->validateCoordinates($latitude, $longitude);
            $this->validateDistance($location, $latitude, $longitude);

            $today = now()->toDateString();
            $schedule = $this->resolveSchedule($employee, $today);

            $record = AttendanceRecord::where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->lockForUpdate()
                ->first();

            if (! $record) {
                throw new AttendanceException('Anda belum melakukan check-in hari ini.');
            }

            if ($record->check_out !== null) {
                throw new AttendanceException('Anda sudah melakukan check-out hari ini.');
            }

            $record->update([
                'check_out' => now()->format('H:i:s'),
                'check_out_latitude' => $latitude,
                'check_out_longitude' => $longitude,
            ]);

            $record->setRelation('attendanceLocation', $location);

            return $record;
        });
    }

    public function todayRecord(Employee $employee): ?AttendanceRecord
    {
        return AttendanceRecord::with('attendanceLocation')
            ->where('employee_id', $employee->id)
            ->whereDate('date', now()->toDateString())
            ->first();
    }

    /**
     * @return array{schedule: ?WorkSchedule, status: string, label: string}
     */
    public function dashboardState(Employee $employee): array
    {
        $record = $this->todayRecord($employee);

        if (! $record) {
            return [
                'schedule' => $this->resolveScheduleOrNull($employee, now()->toDateString()),
                'record' => null,
                'status' => 'belum',
                'label' => 'Belum Absen',
            ];
        }

        if ($record->check_out !== null) {
            $status = 'selesai';
            $label = 'Absen Selesai';
        } else {
            $status = 'hadir';
            $label = $record->status;
        }

        return [
            'record' => $record,
            'status' => $status,
            'label' => $label,
        ];
    }

    /**
     * Ambil jadwal karyawan pada tanggal tertentu (berdasarkan hari).
     */
    public function resolveSchedule(Employee $employee, string $date): ?WorkSchedule
    {
        $schedule = $this->resolveScheduleOrNull($employee, $date);

        if (! $schedule) {
            throw new AttendanceException('Anda belum memiliki jadwal absensi untuk hari ini.');
        }

        return $schedule;
    }

    public function resolveScheduleOrNull(Employee $employee, string $date): ?WorkSchedule
    {
        $dayName = $this->indonesianDayName($date);

        $schedules = $employee->workSchedules()
            ->where('is_active', true)
            ->where('day', $dayName)
            ->orderBy('start_time')
            ->get();

        if ($schedules->isNotEmpty()) {
            return $schedules->first();
        }

        return WorkSchedule::where('is_active', true)
            ->where('day', $dayName)
            ->orderBy('start_time')
            ->first();
    }

    public function indonesianDayName(Carbon|string $date): string
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        return (string) $carbon->locale('id')->isoFormat('dddd');
    }

    private function validateEmployee(Employee $employee): void
    {
        if (! $employee->exists) {
            throw new AttendanceException('Data karyawan tidak ditemukan.');
        }

        if (! $employee->isActive()) {
            throw new AttendanceException('Status karyawan Anda tidak aktif. Silakan hubungi admin.');
        }
    }

    private function resolveLocation(string $publicToken): AttendanceLocation
    {
        $location = AttendanceLocation::where('public_token', $publicToken)->first();

        if (! $location) {
            throw new AttendanceException('QR Code tidak valid atau sudah tidak berlaku.');
        }

        if (! $location->isActive()) {
            throw new AttendanceException('Lokasi absensi sedang tidak aktif.');
        }

        return $location;
    }

    private function validateCoordinates(float $latitude, float $longitude): void
    {
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            throw new AttendanceException('Koordinat GPS tidak valid.');
        }
    }

    private function validateDistance(AttendanceLocation $location, float $latitude, float $longitude): void
    {
        $distance = $this->distance->haversine(
            $location->latitude,
            $location->longitude,
            $latitude,
            $longitude,
        );

        if ($distance === null || $distance > $location->radius) {
            $distanceText = $distance !== null ? round($distance).' meter' : '—';

            throw new AttendanceException(
                "Anda berada di luar radius absensi ({$distanceText} dari lokasi). Radius maksimal {$location->radius} meter.",
            );
        }
    }

    private function determineStatus(WorkSchedule $schedule, Carbon $time): string
    {
        $start = Carbon::parse($schedule->start_time);
        $tolerance = max(0, $schedule->late_tolerance);

        if ($time->lessThanOrEqualTo($start->copy()->addMinutes($tolerance))) {
            return self::STATUS_HADIR;
        }

        return self::STATUS_TERLAMBAT;
    }
}