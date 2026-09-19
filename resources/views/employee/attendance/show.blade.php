@extends('layouts.employee')

@section('title', 'Detail Absensi')

@section('content')
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-bold text-gray-800">Detail Absensi</h1>
            @if ($record->status === 'Terlambat')
                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700">{{ $record->status }}</span>
            @else
                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700">{{ $record->status }}</span>
            @endif
        </div>
        <p class="mt-1 text-sm text-gray-500">{{ $record->date->isoFormat('dddd, D MMMM Y') }}</p>

        <div class="mt-4 grid grid-cols-2 gap-3">
            <div class="rounded-xl bg-blue-50 p-4">
                <p class="text-xs font-bold text-blue-700">Check-in</p>
                <p class="mt-1 text-xl font-bold text-blue-900">{{ $record->check_in ? \Illuminate\Support\Str::substr($record->check_in, 0, 5) : '—' }}</p>
                <p class="mt-1 text-xs text-blue-600">WIB</p>
            </div>
            <div class="rounded-xl bg-indigo-50 p-4">
                <p class="text-xs font-bold text-indigo-700">Check-out</p>
                <p class="mt-1 text-xl font-bold text-indigo-900">{{ $record->check_out ? \Illuminate\Support\Str::substr($record->check_out, 0, 5) : 'Belum' }}</p>
                <p class="mt-1 text-xs text-indigo-600">WIB</p>
            </div>
        </div>

        <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-4">
            <p class="text-xs font-bold text-gray-600">Lokasi Absensi</p>
            <p class="mt-1 font-bold text-gray-800">{{ $record->attendanceLocation->name ?? '—' }}</p>
            <p class="mt-0.5 text-xs text-gray-500">{{ $record->attendanceLocation->address ?? '' }}</p>
        </div>

        <div class="mt-4 space-y-2">
            <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3 text-sm">
                <span class="text-gray-500">Koordinat masuk</span>
                <span class="font-mono text-xs text-gray-700">{{ $record->check_in_latitude }}, {{ $record->check_in_longitude }}</span>
            </div>
            <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3 text-sm">
                <span class="text-gray-500">Koordinat pulang</span>
                <span class="font-mono text-xs text-gray-700">{{ $record->check_out_latitude ?? '—' }}, {{ $record->check_out_longitude ?? '—' }}</span>
            </div>
        </div>

        <a href="{{ route('employee.attendance.history') }}"
            class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
            Kembali ke Riwayat
        </a>
    </div>
@endsection