@extends('layouts.admin')

@section('title', 'Detail Absensi')
@section('header', 'Detail Absensi')
@section('subheader', $record->date->isoFormat('dddd, D MMMM Y'))

@section('content')
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center gap-4 border-b border-gray-100 px-6 py-5">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-600 text-xl font-bold text-white">
                {{ strtoupper(substr($record->employee->user->name, 0, 1)) }}
            </div>
            <div class="flex-1">
                <p class="text-lg font-bold text-gray-800">{{ $record->employee->user->name }}</p>
                <p class="text-sm text-gray-500">
                    {{ $record->employee->employee_number }} • {{ $record->employee->position ?? '—' }} • {{ $record->employee->department ?? '—' }}
                </p>
            </div>
            @if ($record->status === 'Terlambat')
                <span class="rounded-full bg-red-100 px-3 py-1.5 text-sm font-bold text-red-700">{{ $record->status }}</span>
            @else
                <span class="rounded-full bg-green-100 px-3 py-1.5 text-sm font-bold text-green-700">{{ $record->status }}</span>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 p-5">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Tanggal</p>
                        <p class="mt-1 text-sm font-bold text-gray-800">{{ $record->date->isoFormat('D MMMM Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Status</p>
                        <p class="mt-1 text-sm font-bold text-gray-800">{{ $record->status ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Check-in</p>
                        <p class="mt-1 text-sm font-bold text-gray-800">{{ $record->check_in ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Check-out</p>
                        <p class="mt-1 text-sm font-bold text-gray-800">{{ $record->check_out ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Lokasi Absensi</p>
                <p class="mt-1 text-sm font-bold text-gray-800">{{ $record->attendanceLocation->name }}</p>
                <p class="mt-0.5 text-xs text-gray-500">{{ $record->attendanceLocation->address }}</p>
                <p class="mt-1 text-xs text-gray-400">Radius {{ $record->attendanceLocation->radius }} meter ({{ $record->attendanceLocation->latitude }}, {{ $record->attendanceLocation->longitude }})</p>
                @if ($distance !== null)
                    <p class="mt-2 inline-block rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                        Jarak dari lokasi: {{ $distance < 1000 ? round($distance).' meter' : number_format($distance / 1000, 1, ',', '.').' km' }}
                    </p>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 p-5 sm:col-span-2">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-500">Data GPS</p>
                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-xl bg-gray-50 p-3">
                        <p class="text-xs font-semibold text-gray-500">Koordinat Check-in</p>
                        <p class="mt-1 font-mono text-sm text-gray-700">
                            {{ $record->check_in_latitude }}, {{ $record->check_in_longitude }}
                        </p>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-3">
                        <p class="text-xs font-semibold text-gray-500">Koordinat Check-out</p>
                        <p class="mt-1 font-mono text-sm text-gray-700">
                            {{ $record->check_out_latitude ?? '—' }}, {{ $record->check_out_longitude ?? '—' }}
                        </p>
                    </div>
                </div>
            </div>

            @if ($record->notes)
                <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-5 sm:col-span-2">
                    <p class="text-xs font-bold uppercase tracking-wider text-yellow-700">Catatan</p>
                    <p class="mt-1 text-sm text-yellow-800">{{ $record->notes }}</p>
                </div>
            @endif
        </div>

        <div class="border-t border-gray-100 px-6 py-4">
            <a href="{{ route('admin.attendance.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">&larr; Kembali ke Data Absensi</a>
        </div>
    </div>
@endsection