@extends('layouts.admin')

@section('title', 'Laporan Bulanan')
@section('header', 'Laporan Bulanan')
@section('subheader', 'Rekap kehadiran bulanan per karyawan')

@section('content')
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.reports.monthly') }}" class="grid grid-cols-2 gap-3 md:grid-cols-4">
            <div>
                <label for="month" class="text-xs font-semibold text-gray-600">Bulan</label>
                <select name="month" id="month" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected($month === $m)>{{ \Carbon\Carbon::create(null, $m)->locale('id')->translatedFormat('F') }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label for="year" class="text-xs font-semibold text-gray-600">Tahun</label>
                <select name="year" id="year" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    @for ($y = now()->year; $y >= now()->year - 3; $y--)
                        <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label for="department" class="text-xs font-semibold text-gray-600">Department</label>
                <select name="department" id="department" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">Semua Department</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept }}" @selected($department === $dept)>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="employee_id" class="text-xs font-semibold text-gray-600">Karyawan</label>
                <select name="employee_id" id="employee_id" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">Semua Karyawan</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" @selected((string) $employeeId === (string) $employee->id)>
                            {{ $employee->employee_number }} — {{ $employee->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2 flex gap-2 md:col-span-4">
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-bold text-white transition hover:bg-blue-700">Tampilkan</button>
                <a href="{{ route('admin.reports.monthly') }}" class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">Reset</a>
            </div>
        </form>
        <p class="mt-3 text-xs text-gray-500">
            Periode: {{ \Carbon\Carbon::create($year, $month, 1)->locale('id')->translatedFormat('F Y') }}
        </p>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.reports.export-pdf', ['year' => $year, 'month' => $month, 'department' => $department, 'employee_id' => $employeeId ?: null]) }}" class="rounded-lg bg-red-600 px-3.5 py-1.5 text-xs font-bold text-white transition hover:bg-red-700">Export PDF (Ringkasan)</a>
    </div>

    {{-- Table --}}
    <div class="mt-4 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-3">
            <p class="text-sm text-gray-600">
                Menampilkan <span class="font-semibold text-gray-800">{{ $report->firstItem() ?? 0 }}-{{ $report->lastItem() ?? 0 }}</span>
                dari <span class="font-semibold text-gray-800">{{ $report->total() }}</span> karyawan
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3">No</th>
                        <th class="px-5 py-3">Nama Karyawan</th>
                        <th class="px-5 py-3">Nomor</th>
                        <th class="px-5 py-3">Department</th>
                        <th class="px-5 py-3">Total Hari Kerja</th>
                        <th class="px-5 py-3">Hadir</th>
                        <th class="px-5 py-3">Terlambat</th>
                        <th class="px-5 py-3">Tidak Hadir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($report as $index => $employee)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-400">{{ $report->firstItem() + $index }}</td>
                            <td class="px-5 py-3 font-semibold text-gray-800">{{ $employee->user->name }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $employee->employee_number }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $employee->department ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $employee->working_days }}</td>
                            <td class="px-5 py-3 font-semibold text-green-600">{{ $employee->present_count }}</td>
                            <td class="px-5 py-3 font-semibold text-red-600">{{ $employee->late_count }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $employee->absent_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-sm text-gray-500">Tidak ada karyawan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($report->hasPages())
            <div class="border-t border-gray-100 px-5 py-3">
                {{ $report->links() }}
            </div>
        @endif
    </div>

    <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-4 text-xs leading-relaxed text-gray-500 shadow-sm">
        <p class="font-bold text-gray-700">Cara perhitungan</p>
        <ul class="mt-1 list-inside list-disc space-y-1">
            <li><span class="font-semibold text-gray-600">Total Hari Kerja</span> = jumlah hari pada bulan berjalan yang sesuai dengan
                hari jadwal kerja aktif karyawan (relasi jadwal karyawan; jika kosong, menggunakan jadwal global aktif).</li>
            <li><span class="font-semibold text-gray-600">Hadir / Terlambat</span> = jumlah record absensi pada bulan tersebut sesuai status server.</li>
            <li><span class="font-semibold text-gray-600">Tidak Hadir</span> = Total Hari Kerja − total record absensi pada bulan tersebut.</li>
            <li>Status Izin/Sakit/Cuti belum memiliki sumber data terpisah sehingga tidak dihitung terpisah pada laporan bulanan.</li>
        </ul>
    </div>
@endsection