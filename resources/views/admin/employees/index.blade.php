@extends('layouts.admin')

@section('title', 'Data Karyawan')
@section('header', 'Data Karyawan')
@section('subheader', 'Daftar seluruh karyawan')

@section('content')
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-gray-800">Semua Karyawan</h2>
                <p class="text-sm text-gray-500">Total: {{ $employees->total() }} karyawan</p>
            </div>
            <a href="{{ route('admin.employees.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Tambah Karyawan
            </a>
        </div>

        {{-- Search & filter --}}
        <form method="GET" action="{{ route('admin.employees.index') }}" class="flex flex-col gap-3 px-5 py-4 sm:flex-row">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
                <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama, nomor karyawan, email..."
                    class="w-full rounded-lg border border-gray-300 py-2.5 pl-9 pr-3 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
            </div>
            <select name="status" class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                <option value="">Semua Status</option>
                <option value="active" {{ $filterStatus === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ $filterStatus === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-900">Cari</button>
            @if ($search || $filterStatus)
                <a href="{{ route('admin.employees.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">Reset</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3 text-left">No</th>
                        <th class="px-5 py-3 text-left">Nomor Karyawan</th>
                        <th class="px-5 py-3 text-left">Nama</th>
                        <th class="px-5 py-3 text-left">Email</th>
                        <th class="px-5 py-3 text-left">Jabatan</th>
                        <th class="px-5 py-3 text-left">Departemen</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($employees as $index => $employee)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-5 py-4 text-gray-500">{{ $employees->firstItem() + $index }}</td>
                            <td class="px-5 py-4 font-semibold text-gray-800">{{ $employee->employee_number }}</td>
                            <td class="px-5 py-4 font-medium text-gray-900">{{ $employee->user->name }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $employee->user->email }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $employee->position ?? '—' }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $employee->department ?? '—' }}</td>
                            <td class="px-5 py-4">
                                @if ($employee->isActive())
                                    <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Aktif</span>
                                @else
                                    <span class="rounded-full bg-gray-200 px-2.5 py-1 text-xs font-semibold text-gray-600">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.employees.show', $employee) }}" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-100">Detail</a>
                                    <a href="{{ route('admin.employees.edit', $employee) }}" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">Edit</a>
                                    <form method="POST" action="{{ route('admin.employees.toggle', $employee) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ $employee->isActive() ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}">
                                            {{ $employee->isActive() ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16">
                                <div class="text-center">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                                        </svg>
                                    </div>
                                    <p class="mt-3 font-semibold text-gray-700">Belum ada data karyawan</p>
                                    <p class="mt-1 text-sm text-gray-500">
                                        @if ($search || $filterStatus)
                                            Tidak ditemukan hasil untuk pencarian ini.
                                        @else
                                            Tambahkan karyawan pertama Anda untuk memulai.
                                        @endif
                                    </p>
                                    @if (empty($search) && empty($filterStatus))
                                        <a href="{{ route('admin.employees.create') }}" class="mt-4 inline-block rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Tambah Karyawan</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($employees->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
@endsection