@extends('layouts.admin')

@section('title', 'Data Absensi')
@section('header', 'Data Absensi')
@section('subheader', 'Rekap kehadiran seluruh karyawan')

@section('content')
    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-gray-500">Total Hari Ini</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total_hari_ini'] }}</p>
        </div>
        <div class="rounded-2xl border border-green-100 bg-green-50 p-5">
            <p class="text-sm font-semibold text-green-700">Hadir Hari Ini</p>
            <p class="mt-2 text-3xl font-bold text-green-700">{{ $stats['hadir_hari_ini'] }}</p>
        </div>
        <div class="rounded-2xl border border-red-100 bg-red-50 p-5">
            <p class="text-sm font-semibold text-red-700">Terlambat Hari Ini</p>
            <p class="mt-2 text-3xl font-bold text-red-700">{{ $stats['terlambat_hari_ini'] }}</p>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5">
            <p class="text-sm font-semibold text-blue-700">Belum Absen</p>
            <p class="mt-2 text-3xl font-bold text-blue-700">{{ max(0, $stats['total_karyawan_aktif'] - $stats['total_hari_ini']) }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.attendance.index') }}" class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
            <div>
                <label for="filter" class="text-xs font-semibold text-gray-600">Periode</label>
                <select name="filter" id="filter" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="semua" @selected($filters['filter'] === 'semua')>Semua Data</option>
                    <option value="hari_ini" @selected($filters['filter'] === 'hari_ini')>Hari Ini</option>
                    <option value="kemarin" @selected($filters['filter'] === 'kemarin')>Kemarin</option>
                    <option value="minggu_ini" @selected($filters['filter'] === 'minggu_ini')>Minggu Ini</option>
                    <option value="bulan_ini" @selected($filters['filter'] === 'bulan_ini')>Bulan Ini</option>
                    <option value="range" @selected(($filters['date_from'] && $filters['date_to']))>Rentang Tanggal</option>
                </select>
            </div>
            <div>
                <label for="date_from" class="text-xs font-semibold text-gray-600">Dari</label>
                <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <div>
                <label for="date_to" class="text-xs font-semibold text-gray-600">Sampai</label>
                <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] }}" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <div>
                <label for="status" class="text-xs font-semibold text-gray-600">Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="semua" @selected($filters['status'] === 'semua')>Semua</option>
                    <option value="Hadir" @selected($filters['status'] === 'Hadir')>Hadir</option>
                    <option value="Terlambat" @selected($filters['status'] === 'Terlambat')>Terlambat</option>
                    <option value="tidak_hadir" @selected($filters['status'] === 'tidak_hadir')>Tidak Hadir</option>
                </select>
            </div>
            <div>
                <label for="employee_id" class="text-xs font-semibold text-gray-600">Karyawan</label>
                <select name="employee_id" id="employee_id" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">Semua Karyawan</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" @selected((string) $filters['employee_id'] === (string) $employee->id)>
                            {{ $employee->employee_number }} — {{ $employee->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="location_id" class="text-xs font-semibold text-gray-600">Lokasi</label>
                <select name="location_id" id="location_id" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">Semua Lokasi</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" @selected((string) $filters['location_id'] === (string) $location->id)>{{ $location->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2 md:col-span-3 xl:col-span-6">
                <label for="search" class="text-xs font-semibold text-gray-600">Cari Karyawan (nama / nomor)</label>
                <input type="text" id="search" name="search" value="{{ $filters['search'] }}" placeholder="Contoh: Budi atau EMP-0002"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="col-span-2 flex gap-2 md:col-span-3 xl:col-span-6">
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-bold text-white transition hover:bg-blue-700">Terapkan Filter</button>
                <a href="{{ route('admin.attendance.index') }}" class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">Reset</a>
            </div>
        </form>

        {{-- Export toolbar --}}
        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4">
            <span class="text-xs font-semibold text-gray-500">Ekspor sesuai filter:</span>
            <a href="{{ route('admin.reports.export-csv', request()->query()) }}" class="rounded-lg bg-green-600 px-3.5 py-1.5 text-xs font-bold text-white transition hover:bg-green-700">CSV</a>
            <a href="{{ route('admin.reports.export-excel', request()->query()) }}" class="rounded-lg bg-emerald-700 px-3.5 py-1.5 text-xs font-bold text-white transition hover:bg-emerald-800">Excel</a>
            <a href="{{ route('admin.reports.export-pdf', request()->query()) }}" class="rounded-lg bg-red-600 px-3.5 py-1.5 text-xs font-bold text-white transition hover:bg-red-700">PDF</a>
            <a href="{{ route('admin.reports.print', request()->query()) }}" target="_blank" class="rounded-lg bg-gray-700 px-3.5 py-1.5 text-xs font-bold text-white transition hover:bg-gray-800">Print</a>
        </div>
    </div>

    {{-- Table --}}
    <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3">
            <p class="text-sm text-gray-600">
                @if ($isAbsent)
                    Menampilkan data karyawan yang <span class="font-semibold text-gray-800">tidak hadir</span>
                @else
                    Menampilkan <span class="font-semibold text-gray-800">{{ $from }}-{{ $to }}</span> dari
                    <span class="font-semibold text-gray-800">{{ $total }}</span> data
                @endif
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3">No</th>
                        <th class="px-5 py-3">Karyawan</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Lokasi</th>
                        <th class="px-5 py-3">Masuk</th>
                        <th class="px-5 py-3">Pulang</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @if ($isAbsent)
                        @forelse ($records as $index => $employee)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 text-gray-400">{{ $records->firstItem() + $index }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">
                                            {{ strtoupper(substr($employee->user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800">{{ $employee->user->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $employee->employee_number }} • {{ $employee->department ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-gray-700">—</td>
                                <td class="px-5 py-3 text-gray-700">—</td>
                                <td class="px-5 py-3 text-gray-700">—</td>
                                <td class="px-5 py-3 text-gray-700">—</td>
                                <td class="px-5 py-3">
                                    <span class="inline-block rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">Tidak Hadir</span>
                                </td>
                                <td class="px-5 py-3 text-right text-gray-400">—</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-10 text-center text-sm text-gray-500">Tidak ada data karyawan tidak hadir.</td>
                            </tr>
                        @endforelse
                    @else
                    @forelse ($records as $index => $record)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-400">{{ $records->firstItem() + $index }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">
                                        {{ strtoupper(substr($record->employee->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $record->employee->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $record->employee->employee_number }} • {{ $record->employee->department ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-gray-700">{{ $record->date->isoFormat('D MMMM Y') }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $record->attendanceLocation->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $record->check_in ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ $record->check_out ?? '—' }}</td>
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
                                <td colspan="8" class="px-5 py-10 text-center text-sm text-gray-500">Tidak ada data absensi.</td>
                            </tr>
                        @endforelse
                    @endif
                </tbody>
            </table>
        </div>
        @if ($records->hasPages())
            <div class="border-t border-gray-100 px-5 py-3">
                {{ $records->links() }}
            </div>
        @endif
    </div>

    @if ($isAbsent)
        <p class="mt-3 text-xs text-gray-500">
            Catatan: "Tidak Hadir" menampilkan karyawan aktif yang memiliki jadwal kerja sesuai periode dipilih namun belum
            memiliki catatan absensi pada periode tersebut. Pastikan rentang tanggal dipilih untuk hasil yang akurat.
        </p>
    @endif
@endsection