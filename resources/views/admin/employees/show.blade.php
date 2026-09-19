@extends('layouts.admin')

@section('title', 'Detail Karyawan')
@section('header', 'Detail Karyawan')
@section('subheader', $employee->user->name)

@section('content')
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-gray-100 p-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-blue-600 text-2xl font-bold text-white">
                    {{ strtoupper(substr($employee->user->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $employee->user->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $employee->employee_number }} • {{ $employee->position ?? 'Tanpa jabatan' }}</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($employee->isActive())
                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Aktif</span>
                @else
                    <span class="rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-600">Nonaktif</span>
                @endif
                <a href="{{ route('admin.employees.edit', $employee) }}" class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">Edit</a>
                <form method="POST" action="{{ route('admin.employees.toggle', $employee) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="rounded-lg border px-4 py-2 text-sm font-semibold transition {{ $employee->isActive() ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}">
                        {{ $employee->isActive() ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Nomor / NIK Karyawan</p>
                <p class="mt-1 font-semibold text-gray-800">{{ $employee->employee_number }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Email</p>
                <p class="mt-1 font-semibold text-gray-800">{{ $employee->user->email }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Nomor HP</p>
                <p class="mt-1 font-semibold text-gray-800">{{ $employee->phone ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Jabatan</p>
                <p class="mt-1 font-semibold text-gray-800">{{ $employee->position ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Departemen</p>
                <p class="mt-1 font-semibold text-gray-800">{{ $employee->department ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Tanggal Bergabung</p>
                <p class="mt-1 font-semibold text-gray-800">{{ $employee->join_date ? $employee->join_date->format('d F Y') : '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Role Akun</p>
                <p class="mt-1 font-semibold text-gray-800">{{ ucfirst($employee->user->role) }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Status</p>
                <p class="mt-1 font-semibold text-gray-800">{{ $employee->isActive() ? 'Aktif' : 'Nonaktif' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Terdaftar</p>
                <p class="mt-1 font-semibold text-gray-800">{{ $employee->created_at->format('d M Y H:i') }}</p>
            </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-gray-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('admin.employees.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">&larr; Kembali ke daftar</a>
            <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}"
                onsubmit="return confirm('Yakin ingin menghapus karyawan ini beserta akunnya? Tindakan ini tidak dapat dibatalkan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100">Hapus Karyawan</button>
            </form>
        </div>
    </div>
@endsection