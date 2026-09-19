@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Dashboard')
@section('subheader', 'Ringkasan data sistem')

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {{-- Total Karyawan --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-gray-500">Total Karyawan</p>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $totalEmployees }}</p>
        </div>

        {{-- Karyawan Aktif --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-gray-500">Karyawan Aktif</p>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-50 text-green-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $activeEmployees }}</p>
        </div>

        {{-- Total Lokasi --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-gray-500">Total Lokasi</p>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $totalLocations }}</p>
        </div>

        {{-- Lokasi Aktif --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-gray-500">Lokasi Aktif</p>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-50 text-cyan-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.894c-3.808-3.808-3.808-9.98 0-13.789m13.788 0c3.808 3.808 3.808 9.98 0 13.789M12 12h.008v.007H12v-.007z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold text-gray-900">{{ $activeLocations }}</p>
        </div>
    </div>

    {{-- Statistik absensi --}}
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-green-100 bg-green-50 p-5">
            <p class="text-sm font-semibold text-green-700">Hadir Hari Ini</p>
            <p class="mt-2 text-3xl font-bold text-green-700">{{ $presentToday }}</p>
        </div>
        <div class="rounded-2xl border border-red-100 bg-red-50 p-5">
            <p class="text-sm font-semibold text-red-700">Terlambat Hari Ini</p>
            <p class="mt-2 text-3xl font-bold text-red-700">{{ $lateToday }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
            <p class="text-sm font-semibold text-gray-600">Tidak Hadir Hari Ini</p>
            <p class="mt-2 text-3xl font-bold text-gray-700">{{ $absentToday }}</p>
        </div>
        <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-5">
            <p class="text-sm font-semibold text-indigo-700">Persentase Kehadiran</p>
            <p class="mt-2 text-3xl font-bold text-indigo-700">{{ $attendanceRate }}%</p>
            <p class="mt-1 text-xs text-indigo-600">dari {{ $activeEmployees }} karyawan aktif</p>
        </div>
    </div>

    {{-- Grafik 7 hari terakhir --}}
    <div class="mt-6 rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="font-bold text-gray-800">Absensi 7 Hari Terakhir</h2>
            <p class="text-xs text-gray-500">Jumlah Hadir dan Terlambat per hari</p>
        </div>
        <div class="p-5">
            <div class="relative h-64">
                <canvas id="attendance-weekly-chart"></canvas>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Lokasi Aktif --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <h2 class="font-bold text-gray-800">Lokasi Aktif Terbaru</h2>
                <a href="{{ route('admin.locations.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">Lihat semua</a>
            </div>
            <div class="p-5">
                @forelse ($activeLocationsList as $location)
                    <a href="{{ route('admin.locations.qr', $location) }}" class="flex items-center justify-between rounded-xl px-3 py-3 transition hover:bg-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">{{ $location->name }}</p>
                                <p class="text-xs text-gray-500">{{ $location->radius }} meter</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Aktif</span>
                    </a>
                @empty
                    <div class="py-8 text-center text-sm text-gray-500">
                        <p class="font-semibold">Belum ada lokasi aktif.</p>
                        <a href="{{ route('admin.locations.create') }}" class="mt-2 inline-block font-semibold text-blue-600 hover:text-blue-800">Tambah lokasi sekarang</a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Karyawan Terbaru --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <h2 class="font-bold text-gray-800">Karyawan Terbaru</h2>
                <a href="{{ route('admin.employees.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">Lihat semua</a>
            </div>
            <div class="p-5">
                @forelse ($recentEmployees as $employee)
                    <a href="{{ route('admin.employees.show', $employee) }}" class="flex items-center justify-between rounded-xl px-3 py-3 transition hover:bg-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white">
                                {{ strtoupper(substr($employee->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">{{ $employee->user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $employee->employee_number }} • {{ $employee->position ?? '—' }}</p>
                            </div>
                        </div>
                        @if ($employee->isActive())
                            <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Aktif</span>
                        @else
                            <span class="rounded-full bg-gray-200 px-2.5 py-1 text-xs font-semibold text-gray-600">Nonaktif</span>
                        @endif
                    </a>
                @empty
                    <div class="py-8 text-center text-sm text-gray-500">
                        <p class="font-semibold">Belum ada karyawan.</p>
                        <a href="{{ route('admin.employees.create') }}" class="mt-2 inline-block font-semibold text-blue-600 hover:text-blue-800">Tambah karyawan sekarang</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/chart.js'])
    <script>
        (function () {
            const labels = @json(collect($weeklyTrend)->pluck('label')->values());
            const hadir = @json(collect($weeklyTrend)->pluck('hadir')->values());
            const terlambat = @json(collect($weeklyTrend)->pluck('terlambat')->values());
            window.renderAttendanceChart('attendance-weekly-chart', labels, hadir, terlambat);
        })();
    </script>
@endpush