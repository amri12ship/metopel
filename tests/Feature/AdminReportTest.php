<?php

namespace Tests\Feature;

use App\Models\AttendanceLocation;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-01-15 07:55:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function createAttendance(Employee $employee, string $date, string $status = 'Hadir', string $checkIn = '08:00:00'): AttendanceRecord
    {
        return AttendanceRecord::factory()->create([
            'employee_id' => $employee->id,
            'date' => $date,
            'status' => $status,
            'check_in' => $checkIn,
        ]);
    }

    public function test_admin_can_open_daily_report(): void
    {
        $employee = Employee::factory()->create(['department' => 'TI']);
        $this->createAttendance($employee, '2026-01-15');

        $this->actingAs($this->admin())
            ->get(route('admin.reports.daily', ['date' => '2026-01-15']))
            ->assertOk()
            ->assertSee('Laporan Harian')
            ->assertSee('Total Karyawan')
            ->assertSee('Persentase Kehadiran');
    }

    public function test_admin_can_open_monthly_report(): void
    {
        $employee = Employee::factory()->create(['department' => 'TI']);
        $this->createAttendance($employee, '2026-01-15');

        $this->actingAs($this->admin())
            ->get(route('admin.reports.monthly', ['year' => 2026, 'month' => 1]))
            ->assertOk()
            ->assertSee('Laporan Bulanan')
            ->assertSee('Total Hari Kerja');
    }

    public function test_admin_can_open_monthly_report_with_department_and_employee_filter(): void
    {
        $employee = Employee::factory()->create(['department' => 'TI']);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.reports.monthly', [
                'year' => 2026,
                'month' => 1,
                'department' => 'TI',
                'employee_id' => $employee->id,
            ]));

        $response->assertOk()->assertSee($employee->user->name);
    }

    public function test_attendance_index_search_by_name_and_employee_number(): void
    {
        $employee = Employee::factory()->create(['employee_number' => 'EMP-7777']);
        $this->createAttendance($employee, '2026-01-15');
        $other = Employee::factory()->create();
        $this->createAttendance($other, '2026-01-15');

        $byName = $this->actingAs($this->admin())
            ->get(route('admin.attendance.index', ['search' => $employee->user->name]));

        $byName->assertOk()->assertSee($employee->user->name)->assertSee('Menampilkan');

        $byNumber = $this->actingAs($this->admin())
            ->get(route('admin.attendance.index', ['search' => 'EMP-7777']));

        $byNumber->assertOk()->assertSee($employee->user->name);
    }

    public function test_attendance_index_filter_by_status(): void
    {
        $employee = Employee::factory()->create();
        $this->createAttendance($employee, '2026-01-15', 'Hadir');
        $lateGuy = Employee::factory()->create();
        $this->createAttendance($lateGuy, '2026-01-15', 'Terlambat', '08:30:00');

        $response = $this->actingAs($this->admin())
            ->get(route('admin.attendance.index', ['status' => 'Terlambat', 'filter' => 'hari_ini']));

        $response->assertOk()->assertSee($lateGuy->user->name);

        preg_match('/<tbody\b[^>]*>(.*?)<\/tbody>/s', $response->getContent(), $matches);
        $this->assertNotEmpty($matches);
        $this->assertStringContainsString($lateGuy->user->name, $matches[1]);
        $this->assertStringNotContainsString($employee->user->name, $matches[1]);
    }

    public function test_attendance_index_filter_by_employee_and_location(): void
    {
        $locationA = AttendanceLocation::factory()->create(['name' => 'Lokasi A']);
        $locationB = AttendanceLocation::factory()->create(['name' => 'Lokasi B']);
        $employee = Employee::factory()->create();
        AttendanceRecord::factory()->create([
            'employee_id' => $employee->id,
            'attendance_location_id' => $locationA->id,
            'date' => '2026-01-15',
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.attendance.index', [
                'employee_id' => $employee->id,
                'location_id' => $locationA->id,
            ]));

        $response->assertOk()->assertSee($employee->user->name);

        $empty = $this->actingAs($this->admin())
            ->get(route('admin.attendance.index', [
                'employee_id' => $employee->id,
                'location_id' => $locationB->id,
            ]));

        $empty->assertOk()->assertSee('Tidak ada data absensi.');
    }

    public function test_attendance_index_tidak_hadir_filter_shows_employee_without_record(): void
    {
        $hasRecord = Employee::factory()->create();
        $this->createAttendance($hasRecord, '2026-01-15');
        $absent = Employee::factory()->create();
        $this->attachSchedule($absent);
        $this->attachSchedule($hasRecord);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.attendance.index', [
                'status' => 'tidak_hadir',
                'date_from' => '2026-01-15',
                'date_to' => '2026-01-15',
            ]));

        $response->assertOk()
            ->assertSee($absent->user->name)
            ->assertSee('Tidak Hadir');
    }

    public function test_export_csv_contains_filtered_rows(): void
    {
        $employee = Employee::factory()->create(['department' => 'TI']);
        $this->createAttendance($employee, '2026-01-15');

        $response = $this->actingAs($this->admin())
            ->get(route('admin.reports.export-csv', ['date_from' => '2026-01-15', 'date_to' => '2026-01-15']));

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertDownload('laporan-absensi-2026-01-15.csv');
    }

    public function test_export_excel_downloads_file(): void
    {
        $employee = Employee::factory()->create();
        $this->createAttendance($employee, '2026-01-15');

        $response = $this->actingAs($this->admin())
            ->get(route('admin.reports.export-excel', ['date_from' => '2026-01-15', 'date_to' => '2026-01-15']));

        $response->assertOk()
            ->assertDownload('laporan-absensi-2026-01-15.xlsx');
    }

    public function test_export_pdf_downloads_file(): void
    {
        $employee = Employee::factory()->create();
        $this->createAttendance($employee, '2026-01-15');

        $response = $this->actingAs($this->admin())
            ->get(route('admin.reports.export-pdf', ['date_from' => '2026-01-15', 'date_to' => '2026-01-15']));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('laporan-absensi-2026-01-15.pdf');
    }

    public function test_print_page_renders_without_sidebar(): void
    {
        $employee = Employee::factory()->create();
        $this->createAttendance($employee, '2026-01-15');

        $response = $this->actingAs($this->admin())
            ->get(route('admin.reports.print', ['date_from' => '2026-01-15', 'date_to' => '2026-01-15']));

        $response->assertOk()
            ->assertSee('LAPORAN ABSENSI KARYAWAN')
            ->assertSee('Tanggal cetak')
            ->assertSee($employee->user->name);
    }

    public function test_employee_cannot_access_reports_and_exports(): void
    {
        $employeeUser = User::factory()->create();

        $this->actingAs($employeeUser)
            ->get(route('admin.reports.daily'))
            ->assertForbidden();

        $this->actingAs($employeeUser)
            ->get(route('admin.reports.export-csv'))
            ->assertForbidden();

        $this->actingAs($employeeUser)
            ->get(route('admin.reports.export-pdf'))
            ->assertForbidden();
    }

    public function test_admin_dashboard_shows_attendance_rate_and_weekly_chart(): void
    {
        $employee = Employee::factory()->create();
        $this->createAttendance($employee, now()->toDateString());

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Persentase Kehadiran')
            ->assertSee('Absensi 7 Hari Terakhir');
    }

    public function test_attendance_index_paginates_reports_summary(): void
    {
        $employee = Employee::factory()->create();
        for ($i = 0; $i < 20; $i++) {
            $this->createAttendance($employee, '2026-01-'.$this->pad($i + 1));
        }

        $response = $this->actingAs($this->admin())
            ->get(route('admin.attendance.index', ['date_from' => '2026-01-01', 'date_to' => '2026-01-31']));

        $response->assertOk()->assertSee('Menampilkan');
    }

    private function pad(int $day): string
    {
        return str_pad((string) $day, 2, '0', STR_PAD_LEFT);
    }

    private function attachSchedule(Employee $employee): void
    {
        $day = Carbon::parse('2026-01-15')->locale('id')->isoFormat('dddd');
        $schedule = WorkSchedule::factory()->create([
            'day' => $day,
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'late_tolerance' => 15,
        ]);

        $employee->workSchedules()->attach($schedule);
    }
}