<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AttendanceExport;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\AttendanceReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly AttendanceReportService $reportService,
    ) {
    }

    public function daily(Request $request): View
    {
        $date = $request->string('date', now()->toDateString())->toString();

        if (! $this->isValidDate($date)) {
            $date = now()->toDateString();
        }

        $summary = $this->reportService->dailySummary($date);
        $records = $this->reportService->recordsQuery([
            'date_from' => $date,
            'date_to' => $date,
        ])->paginate(20)->withQueryString();

        return view('admin.reports.daily', compact('date', 'summary', 'records'));
    }

    public function monthly(Request $request): View
    {
        $year = (int) $request->string('year', (string) now()->year)->toString();
        $month = (int) $request->string('month', (string) now()->month)->toString();

        if (! checkdate($month, 1, $year)) {
            $year = now()->year;
            $month = now()->month;
        }

        $employeeId = $request->filled('employee_id') ? (int) $request->input('employee_id') : null;
        $department = $request->string('department')->toString();

        $report = $this->reportService->monthlyReport($year, $month, $employeeId, $department);

        $departments = Employee::query()
            ->whereNotNull('department')
            ->select('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        $employees = Employee::with('user')
            ->orderBy('employee_number')
            ->get();

        return view('admin.reports.monthly', compact(
            'report',
            'year',
            'month',
            'department',
            'employeeId',
            'departments',
            'employees',
        ));
    }

    public function print(Request $request): View
    {
        $filters = $request->query();
        $rows = $this->reportService->rows($filters);

        return view('admin.reports.print', [
            'rows' => $rows,
            'isAbsent' => $this->reportService->isTidakHadirFilter($filters),
            'title' => $this->reportService->periodLabel($filters),
            'printedAt' => now()->isoFormat('dddd, D MMMM Y HH:mm'),
            'generatedBy' => auth()->user()->name,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $request->query();
        $rows = $this->reportService->rows($filters);
        $filename = 'laporan-absensi-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Nama Karyawan',
                'Nomor Karyawan',
                'Tanggal',
                'Check-in',
                'Check-out',
                'Lokasi',
                'Status',
                'Latitude Check-in',
                'Longitude Check-in',
                'Latitude Check-out',
                'Longitude Check-out',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['employee_name'],
                    $row['employee_number'],
                    $row['date'] ?? '',
                    $row['check_in'] ?? '',
                    $row['check_out'] ?? '',
                    $row['location'],
                    $row['status'],
                    $row['check_in_latitude'] ?? '',
                    $row['check_in_longitude'] ?? '',
                    $row['check_out_latitude'] ?? '',
                    $row['check_out_longitude'] ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $rows = $this->reportService->rows($request->query())->all();
        $filename = 'laporan-absensi-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new AttendanceExport($rows), $filename);
    }

    public function exportPdf(Request $request): Response
    {
        $filters = $request->query();
        $rows = $this->reportService->rows($filters);
        $filename = 'laporan-absensi-'.now()->format('Y-m-d').'.pdf';

        $pdf = Pdf::loadView('admin.reports.pdf', [
            'rows' => $rows,
            'isAbsent' => $this->reportService->isTidakHadirFilter($filters),
            'title' => $this->reportService->periodLabel($filters),
            'printedAt' => now()->isoFormat('dddd, D MMMM Y HH:mm'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    private function isValidDate(string $date): bool
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        try {
            return (string) \Carbon\Carbon::parse($date)->format('Y-m-d') === $date;
        } catch (\Throwable) {
            return false;
        }
    }
}