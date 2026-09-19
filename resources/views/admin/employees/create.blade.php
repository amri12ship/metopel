@extends('layouts.admin')

@section('title', 'Tambah Karyawan')
@section('header', 'Tambah Karyawan')
@section('subheader', 'Buat akun dan data karyawan baru')

@section('content')
    <div class="mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-6 py-4">
            <h2 class="font-bold text-gray-800">Form Tambah Karyawan</h2>
            <p class="text-sm text-gray-500">Akun user (role karyawan) dibuat otomatis bersama data karyawan.</p>
        </div>

        <form method="POST" action="{{ route('admin.employees.store') }}" class="space-y-5 p-6">
            @csrf

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="employee_number" class="mb-1 block text-sm font-semibold text-gray-700">Nomor / NIK Karyawan *</label>
                    <input id="employee_number" name="employee_number" type="text" value="{{ old('employee_number') }}" required
                        placeholder="EMP-0001"
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('employee_number')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="name" class="mb-1 block text-sm font-semibold text-gray-700">Nama Lengkap *</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('name')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm font-semibold text-gray-700">Email *</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('email')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="phone" class="mb-1 block text-sm font-semibold text-gray-700">Nomor HP</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone') }}" placeholder="08XXXXXXXXXX"
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('phone')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="position" class="mb-1 block text-sm font-semibold text-gray-700">Jabatan</label>
                    <input id="position" name="position" type="text" value="{{ old('position') }}"
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('position')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="department" class="mb-1 block text-sm font-semibold text-gray-700">Departemen</label>
                    <input id="department" name="department" type="text" value="{{ old('department') }}"
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('department')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="join_date" class="mb-1 block text-sm font-semibold text-gray-700">Tanggal Bergabung</label>
                    <input id="join_date" name="join_date" type="date" value="{{ old('join_date') }}"
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('join_date')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="status" class="mb-1 block text-sm font-semibold text-gray-700">Status *</label>
                    <select id="status" name="status" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                        <option value="active" {{ old('status') === 'inactive' ? '' : 'selected' }}>Aktif</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="password" class="mb-1 block text-sm font-semibold text-gray-700">Password *</label>
                    <input id="password" name="password" type="password" required
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    <p class="mt-1 text-xs text-gray-400">Minimal 8 karakter. Password tersimpan ter-hash.</p>
                    @error('password')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="password_confirmation" class="mb-1 block text-sm font-semibold text-gray-700">Konfirmasi Password *</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5">
                <a href="{{ route('admin.employees.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">Batal</a>
                <button type="submit" class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan Karyawan</button>
            </div>
        </form>
    </div>
@endsection