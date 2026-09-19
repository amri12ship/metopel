@extends('layouts.employee')

@section('title', 'Dashboard Karyawan')

@section('content')
    {{-- Greeting + clock --}}
    <div class="rounded-2xl bg-gradient-to-br from-blue-700 to-blue-900 p-5 text-white shadow-sm">
        <p class="text-sm text-blue-200">Selamat {{ \Carbon\Carbon::now()->locale('id')->isoFormat('A') }},</p>
        <h1 class="mt-1 truncate text-xl font-bold">{{ $employee->user->name }}</h1>
        <p class="mt-0.5 text-xs text-blue-200">{{ $employee->employee_number }} • {{ $employee->position ?? '—' }}</p>

        <div class="mt-4 flex items-end justify-between">
            <div>
                <p class="font-mono text-3xl font-bold tabular-nums" id="employee-clock">{{ now()->format('H:i:s') }}</p>
                <p class="text-xs text-blue-200">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
            </div>
            <div id="employee-status-pill"
                class="rounded-full px-3 py-1.5 text-xs font-bold {{ $state['status'] === 'hadir' ? 'bg-green-500/20 text-green-200' : ($state['status'] === 'selesai' ? 'bg-indigo-500/20 text-indigo-200' : 'bg-white/15 text-blue-100') }}">
                {{ $state['label'] }}
            </div>
        </div>
    </div>

    {{-- Today's schedule --}}
    <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <h2 class="text-sm font-bold text-gray-700">Jadwal Hari Ini</h2>
        @if ($schedule)
            <div class="mt-2 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">{{ $schedule->name }}</p>
                        <p class="text-xs text-gray-500">{{ \Illuminate\Support\Str::substr($schedule->start_time, 0, 5) }} – {{ \Illuminate\Support\Str::substr($schedule->end_time, 0, 5) }} WIB</p>
                    </div>
                </div>
                <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Aktif</span>
            </div>
        @else
            <p class="mt-2 text-sm text-gray-500">Belum ada jadwal untuk hari ini.</p>
        @endif
    </div>

    {{-- Action buttons --}}
    <div class="mt-4 grid grid-cols-2 gap-3">
        @if ($state['status'] === 'belum')
            <a href="{{ route('employee.attendance.index') }}?mode=masuk"
                class="flex flex-col items-center gap-2 rounded-2xl bg-blue-600 p-4 text-white shadow-sm transition active:scale-[0.98]">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-bold">ABSEN MASUK</span>
            </a>
            <div class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-gray-50 p-4 text-gray-400">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12l14 0"/>
                </svg>
                <span class="text-sm font-bold">ABSEN PULANG</span>
            </div>
        @elseif ($state['status'] === 'hadir')
            <div class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-green-200 bg-green-50 p-4 text-green-600">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-bold">SUDAH ABSEN MASUK</span>
            </div>
            <a href="{{ route('employee.attendance.index') }}?mode=pulang"
                class="flex flex-col items-center gap-2 rounded-2xl bg-indigo-600 p-4 text-white shadow-sm transition active:scale-[0.98]">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 15l-5 5-5-5m5 5V4"/>
                </svg>
                <span class="text-sm font-bold">ABSEN PULANG</span>
            </a>
        @else
            <div class="col-span-2 flex flex-col items-center gap-2 rounded-2xl border border-indigo-200 bg-indigo-50 p-4 text-indigo-600">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-bold">Absensi Anda hari ini sudah selesai</span>
            </div>
        @endif
    </div>

    @if ($state['record'])
        <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700">Status Hari Ini</h2>
            <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                <div class="rounded-xl bg-gray-50 p-3">
                    <p class="text-[11px] font-semibold text-gray-500">Status</p>
                    <p class="mt-1 text-sm font-bold {{ $state['record']->status === 'Terlambat' ? 'text-red-600' : 'text-green-600' }}">
                        {{ $state['record']->status ?? '—' }}
                    </p>
                </div>
                <div class="rounded-xl bg-gray-50 p-3">
                    <p class="text-[11px] font-semibold text-gray-500">Masuk</p>
                    <p class="mt-1 text-sm font-bold text-gray-800">{{ $state['record']->check_in ? \Illuminate\Support\Str::substr($state['record']->check_in, 0, 5) : '—' }}</p>
                </div>
                <div class="rounded-xl bg-gray-50 p-3">
                    <p class="text-[11px] font-semibold text-gray-500">Pulang</p>
                    <p class="mt-1 text-sm font-bold text-gray-800">{{ $state['record']->check_out ? \Illuminate\Support\Str::substr($state['record']->check_out, 0, 5) : '—' }}</p>
                </div>
            </div>
            @if ($state['record']->attendanceLocation)
                <p class="mt-3 flex items-center gap-1.5 text-xs text-gray-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                    </svg>
                    {{ $state['record']->attendanceLocation->name }}
                </p>
            @endif
        </div>
    @endif

    {{-- Recent history --}}
    <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-700">Riwayat Terakhir</h2>
            <a href="{{ route('employee.attendance.history') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">Lihat semua</a>
        </div>
        <div class="mt-2 divide-y divide-gray-100">
            @forelse ($recentHistory as $record)
                <a href="{{ route('employee.attendance.show', $record) }}" class="flex items-center justify-between py-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $record->date->isoFormat('D MMMM Y') }}</p>
                        <p class="text-xs text-gray-500">{{ $record->attendanceLocation->name ?? '—' }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <p class="text-xs text-gray-500">{{ $record->check_in ? \Illuminate\Support\Str::substr($record->check_in, 0, 5) : '—' }}</p>
                        @if ($record->status === 'Terlambat')
                            <span class="rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-semibold text-red-700">{{ $record->status }}</span>
                        @else
                            <span class="rounded-full bg-green-100 px-2.5 py-1 text-[11px] font-semibold text-green-700">{{ $record->status }}</span>
                        @endif
                    </div>
                </a>
            @empty
                <p class="py-6 text-center text-sm text-gray-500">Belum ada riwayat absensi.</p>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const clock = document.getElementById('employee-clock');
            if (clock) {
                setInterval(() => {
                    clock.textContent = new Date().toLocaleTimeString('en-GB', { hour12: false });
                }, 1000);
            }
        })();
    </script>
@endpush