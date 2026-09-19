@extends('layouts.employee')

@section('title', 'Riwayat Absensi')

@section('content')
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <h1 class="text-lg font-bold text-gray-800">Riwayat Absensi</h1>
        <p class="mt-0.5 text-sm text-gray-500">Rekap kehadiran Anda.</p>

        {{-- Filter tabs --}}
        <form method="GET" action="{{ route('employee.attendance.history') }}" class="mt-4">
            <div class="grid grid-cols-4 gap-1 rounded-xl bg-gray-100 p-1">
                @foreach (['semua' => 'Semua', 'hari_ini' => 'Hari Ini', 'minggu_ini' => 'Minggu Ini', 'bulan_ini' => 'Bulan Ini'] as $key => $label)
                    <button type="submit" name="filter" value="{{ $key }}"
                        class="rounded-lg px-2 py-2 text-xs font-semibold transition {{ $filter === $key ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="mt-3">
                <button type="button" id="toggle-range"
                    class="text-xs font-bold text-blue-600 hover:text-blue-800">
                    {{ $allowRange ? 'Sembunyikan rentang tanggal' : 'Pilih rentang tanggal' }}
                </button>
            </div>

            <div id="range-fields" class="{{ $allowRange ? '' : 'hidden' }} mt-2 grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs font-semibold text-gray-600">Dari</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600">Sampai</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
            </div>
            <input type="hidden" name="show_range" value="{{ $allowRange ? '1' : '0' }}">

            <div class="mt-3 grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs font-semibold text-gray-600">Bulan</label>
                    <input type="month" name="month" value="{{ $month }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600">Status</label>
                    <select name="status" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">Semua</option>
                        <option value="Hadir" @selected($status === 'Hadir')>Hadir</option>
                        <option value="Terlambat" @selected($status === 'Terlambat')>Terlambat</option>
                    </select>
                </div>
            </div>

            <div class="mt-3 flex gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white transition active:scale-[0.98]">Terapkan Filter</button>
                <a href="{{ route('employee.attendance.history') }}" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">Reset</a>
            </div>
        </form>
    </div>

    {{-- Summary --}}
    <div class="mt-4 grid grid-cols-3 gap-2 text-center">
        <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
            <p class="text-[11px] font-semibold text-gray-500">Total Hari</p>
            <p class="mt-1 text-xl font-bold text-gray-800">{{ $records->total() }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
            <p class="text-[11px] font-semibold text-gray-500">Hadir</p>
            <p class="mt-1 text-xl font-bold text-green-600">{{ $records->where('status', 'Hadir')->count() }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm">
            <p class="text-[11px] font-semibold text-gray-500">Terlambat</p>
            <p class="mt-1 text-xl font-bold text-red-600">{{ $records->where('status', 'Terlambat')->count() }}</p>
        </div>
    </div>

    {{-- Records --}}
    <div class="mt-4 space-y-2">
        @forelse ($records as $record)
            <a href="{{ route('employee.attendance.show', $record) }}"
                class="block rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition active:scale-[0.99]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-bold text-gray-800">{{ $record->date->isoFormat('dddd, D MMMM Y') }}</p>
                        <p class="mt-0.5 flex items-center gap-1 text-xs text-gray-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                            </svg>
                            {{ $record->attendanceLocation->name ?? '—' }}
                        </p>
                    </div>
                    @if ($record->status === 'Terlambat')
                        <span class="rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-semibold text-red-700">{{ $record->status }}</span>
                    @else
                        <span class="rounded-full bg-green-100 px-2.5 py-1 text-[11px] font-semibold text-green-700">{{ $record->status }}</span>
                    @endif
                </div>
                <div class="mt-3 flex items-center gap-4 border-t border-gray-100 pt-3 text-sm">
                    <div>
                        <p class="text-[11px] font-semibold text-gray-500">Masuk</p>
                        <p class="font-bold text-gray-800">{{ $record->check_in ? \Illuminate\Support\Str::substr($record->check_in, 0, 5) : '—' }}</p>
                    </div>
                    <svg class="h-4 w-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                    <div>
                        <p class="text-[11px] font-semibold text-gray-500">Pulang</p>
                        <p class="font-bold text-gray-800">{{ $record->check_out ? \Illuminate\Support\Str::substr($record->check_out, 0, 5) : '—' }}</p>
                    </div>
                    <span class="ml-auto rounded-lg bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-600">Detail</span>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-gray-200 bg-white py-10 text-center">
                <p class="text-sm font-semibold text-gray-500">Tidak ada riwayat absensi.</p>
                <p class="mt-1 text-xs text-gray-400">Lakukan absensi melalui halaman Absensi.</p>
            </div>
        @endforelse
    </div>

    @if ($records->hasPages())
        <div class="mt-4">
            {{ $records->links() }}
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        (function () {
            const toggle = document.getElementById('toggle-range');
            const fields = document.getElementById('range-fields');
            const flag = document.querySelector('input[name="show_range"]');

            if (toggle && fields && flag) {
                toggle.addEventListener('click', () => {
                    const hidden = fields.classList.toggle('hidden');
                    flag.value = hidden ? '0' : '1';
                    toggle.textContent = hidden ? 'Pilih rentang tanggal' : 'Sembunyikan rentang tanggal';
                });
            }
        })();
    </script>
@endpush