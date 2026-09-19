@extends('layouts.admin')

@section('title', 'Laporan Harian')
@section('header', 'Laporan Harian')
@section('subheader', 'Rekap kehadiran harian')

@section('content')
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.reports.daily') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="date" class="text-xs font-semibold text-gray-600">Tanggal</label>
                <input type="date" id="date" name="date" value="{{ $date }}"
                    class="mt-1 block rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-bold text-white transition hover:bg-blue-700">Tampilkan</button>
            <span class="self-center text-sm text-gray-500">{{ \Carbon\Carbon::parse($date)->isoFormat('dddd, D MMMM Y') }}</span>
        </form>
    </div>

    {{-- Summary --}}
    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-gray-500">Total Karyawan</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $summary['total_employees'] }}</p>
        </div>
        <div class="rounded-2xl border border-green-100 bg-green-50 p-5">
            <p class="text-sm font-semibold text-green-700">Hadir</p>
            <p class="mt-2 text-3xl font-bold text-green-700">{{ $summary['present'] }}</p>
        </div>
        <div class="rounded-2xl border border-red-100 bg-red-50 p-5">
            <p class="text-sm font-semibold text-red-700">Terlambat</p>
            <p class="mt-2 text-3xl font-bold text-red-700">{{ $summary['late'] }}</p>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5">
            <p class="text-sm font-semibold text-blue-700">Tidak Hadir</p>
            <p class="mt-2 text-3xl font-bold text-blue-700">{{ $summary['absent'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
            <p class="text-sm font-semibold text-gray-600">Persentase Kehadiran</p>
            <p class="mt-2 text-3xl font-bold text-gray-700">{{ $summary['attendance_rate'] }}%</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5">
            <p class="text-sm font-semibold text-amber-700">Total Absen Masuk</p>
            <p class="mt-2 text-3xl font-bold text-amber-700">{{ $summary['total_attended'] }}</p>
        </div>
    </div>

    @if ($summary['other_statuses'])
        <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-gray-600">Status lain yang tercatat pada tanggal ini:</p>
            <div class="mt-2 flex flex-wrap gap-3">
                @foreach ($summary['other_statuses'] as $status => $count)
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700">{{ $status }}: {{ $count }}</span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Export toolbar --}}
    <div class="mt-6 flex flex-wrap items-center gap-2">
        <span class="text-xs font-semibold text-gray-500">Ekspor laporan tanggal {{ \Carbon\Carbon::parse($date)->isoFormat('D MMMM Y') }}:</span>
        <a href="{{ route('admin.reports.export-csv', ['date_from' => $date, 'date_to' => $date]) }}" class="rounded-lg bg-green-600 px-3.5 py-1.5 text-xs font-bold text-white transition hover:bg-green-700">CSV</a>
        <a href="{{ route('admin.reports.export-excel', ['date_from' => $date, 'date_to' => $date]) }}" class="rounded-lg bg-emerald-700 px-3.5 py-1.5 text-xs font-bold text-white transition hover:bg-emerald-800">Excel</a>
        <a href="{{ route('admin.reports.export-pdf', ['date_from' => $date, 'date_to' => $date]) }}" class="rounded-lg bg-red-600 px-3.5 py-1.5 text-xs font-bold text-white transition hover:bg-red-700">PDF</a>
        <a href="{{ route('admin.reports.print', ['date_from' => $date, 'date_to' => $date]) }}" target="_blank" class="rounded-lg bg-gray-700 px-3.5 py-1.5 text-xs font-bold text-white transition hover:bg-gray-800">Print</a>
    </div>

    {{-- Detail records --}}
    <div class="mt-4 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-3">
            <p class="text-sm text-gray-600">
                Detail absensi tanggal {{ \Carbon\Carbon::parse($date)->isoFormat('D MMMM Y') }} —
                Menampilkan <span class="font-semibold text-gray-800">{{ $records->firstItem() ?? 0 }}-{{ $records->lastItem() ?? 0 }}</span>
                dari <span class="font-semibold text-gray-800">{{ $records->total() }}</span> data
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3">No</th>
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Nomor</th>
                        <th class="px-5 py-3">Check-in</th>
                        <th class="px-5 py-3">Check-out</th>
                        <th class="px-5 py-3">Lokasi</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($records as $index => $record)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-400">{{ $records->firstItem() + $index }}</td>
                            <td class="px-5 py-3 font-semibold text-gray-800">{{ $record->employee->user->name }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $record->employee->employee_number }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $record->check_in ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $record->check_out ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $record->attendanceLocation->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                @if ($record->status === 'Terlambat')
                                    <span class="inline-block rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">{{ $record->status }}</span>
                                @else
                                    <span class="inline-block rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">{{ $record->status ?? '—' }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.attendance.show', $record) }}" class="font-semibold text-blue-600 hover:text-blue-800">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-sm text-gray-500">Tidak ada data absensi pada tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($records->hasPages())
            <div class="border-t border-gray-100 px-5 py-3">
                {{ $records->links() }}
            </div>
        @endif
    </div>
@endsection