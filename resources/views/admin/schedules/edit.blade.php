@extends('layouts.admin')

@section('title', 'Edit Jadwal Kerja')
@section('header', 'Edit Jadwal Kerja')
@section('subheader', $schedule->name)

@section('content')
    <div class="mx-auto max-w-2xl rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-6 py-4">
            <h2 class="font-bold text-gray-800">Form Edit Jadwal</h2>
            <p class="text-sm text-gray-500">Perbarui jam kerja dan toleransi keterlambatan.</p>
        </div>

        <form method="POST" action="{{ route('admin.schedules.update', $schedule) }}" class="space-y-5 p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="mb-1 block text-sm font-semibold text-gray-700">Nama Jadwal *</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $schedule->name) }}" required
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('name')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="day" class="mb-1 block text-sm font-semibold text-gray-700">Hari *</label>
                    <input id="day" name="day" type="text" value="{{ old('day', $schedule->day) }}" required
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('day')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="late_tolerance" class="mb-1 block text-sm font-semibold text-gray-700">Toleransi Keterlambatan (menit) *</label>
                    <input id="late_tolerance" name="late_tolerance" type="number" min="0" value="{{ old('late_tolerance', $schedule->late_tolerance) }}" required
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('late_tolerance')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="start_time" class="mb-1 block text-sm font-semibold text-gray-700">Jam Mulai *</label>
                    <input id="start_time" name="start_time" type="time" value="{{ old('start_time', \Illuminate\Support\Carbon::parse($schedule->start_time)->format('H:i')) }}" required
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('start_time')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="end_time" class="mb-1 block text-sm font-semibold text-gray-700">Jam Selesai *</label>
                    <input id="end_time" name="end_time" type="time" value="{{ old('end_time', \Illuminate\Support\Carbon::parse($schedule->end_time)->format('H:i')) }}" required
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('end_time')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="is_active" class="mb-1 block text-sm font-semibold text-gray-700">Status</label>
                    <select id="is_active" name="is_active"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                        <option value="1" {{ old('is_active', $schedule->is_active) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ ! old('is_active', $schedule->is_active) ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('is_active')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                <a href="{{ route('admin.schedules.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">Batal</a>
                <button type="submit" class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection