<?php

namespace Database\Seeders;

use App\Models\AttendanceLocation;
use App\Models\Employee;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    private const DEMO_PASSWORD = '12345678';

    /**
     * Seed the application's database without creating duplicates.
     */
    public function run(): void
    {
        $this->call(AdminSeeder::class);
        $this->seedEmployees();
        $this->seedLocations();
        $this->seedWorkSchedules();
        $this->seedEmployeeSchedules();
    }

    private function seedEmployees(): void
    {
        $employees = [
            ['name' => 'Karyawan Demo', 'email' => 'karyawan@gmail.com', 'employee_number' => 'EMP-0001', 'phone' => '081234567891', 'position' => 'Staff', 'department' => 'Umum'],
            ['name' => 'Budi Santoso', 'email' => 'budi@gmail.com', 'employee_number' => 'EMP-0002', 'phone' => '081234567892', 'position' => 'Marketing', 'department' => 'Pemasaran'],
            ['name' => 'Siti Rahayu', 'email' => 'siti@gmail.com', 'employee_number' => 'EMP-0003', 'phone' => '081234567893', 'position' => 'Keuangan', 'department' => 'Keuangan'],
            ['name' => 'Andi Wijaya', 'email' => 'andi@gmail.com', 'employee_number' => 'EMP-0004', 'phone' => '081234567894', 'position' => 'IT Support', 'department' => 'TI'],
        ];

        foreach ($employees as $index => $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => self::DEMO_PASSWORD,
                    'role' => User::ROLE_EMPLOYEE,
                ],
            );

            Employee::firstOrCreate(
                ['employee_number' => $data['employee_number']],
                [
                    'user_id' => $user->id,
                    'phone' => $data['phone'],
                    'position' => $data['position'],
                    'department' => $data['department'],
                    'join_date' => now()->subMonths($index + 1)->subDays(2)->toDateString(),
                    'status' => Employee::STATUS_ACTIVE,
                ],
            );
        }
    }

    private function seedLocations(): void
    {
        $locations = [
            [
                'name' => 'Kantor Pusat',
                'address' => 'Jl. Contoh No. 1, Medan',
                'latitude' => 3.5952,
                'longitude' => 98.6722,
                'radius' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Cabang Percut Sei Tuan',
                'address' => 'Jl. Raya Percut No. 2, Deli Serdang',
                'latitude' => 3.6714,
                'longitude' => 98.7687,
                'radius' => 200,
                'is_active' => true,
            ],
        ];

        foreach ($locations as $data) {
            AttendanceLocation::firstOrCreate(
                ['name' => $data['name']],
                [
                    'address' => $data['address'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'radius' => $data['radius'],
                    'is_active' => $data['is_active'],
                ],
            );
        }
    }

    private function seedWorkSchedules(): void
    {
        $schedules = [
            ['name' => 'Shift Pagi', 'day' => 'Senin', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'late_tolerance' => 15],
            ['name' => 'Shift Siang', 'day' => 'Senin', 'start_time' => '12:00:00', 'end_time' => '21:00:00', 'late_tolerance' => 10],
            ['name' => 'Shift Pagi', 'day' => 'Selasa', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'late_tolerance' => 15],
            ['name' => 'Shift Pagi', 'day' => 'Rabu', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'late_tolerance' => 15],
            ['name' => 'Shift Pagi', 'day' => 'Kamis', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'late_tolerance' => 15],
            ['name' => 'Shift Pagi', 'day' => 'Jumat', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'late_tolerance' => 15],
        ];

        foreach ($schedules as $data) {
            WorkSchedule::firstOrCreate(
                ['name' => $data['name'], 'day' => $data['day']],
                [
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'late_tolerance' => $data['late_tolerance'],
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedEmployeeSchedules(): void
    {
        $schedules = WorkSchedule::where('is_active', true)->get();
        $employees = Employee::all();

        foreach ($employees as $employee) {
            $employee->workSchedules()->syncWithoutDetaching($schedules->pluck('id'));
        }
    }
}
