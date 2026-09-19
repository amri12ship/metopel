@extends('layouts.employee')

@section('title', 'Profil Saya')

@section('content')
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div>
                <p class="text-lg font-bold text-gray-800">{{ $employee->user->name }}</p>
                <p class="text-xs text-gray-500">{{ $employee->employee_number }} • {{ $employee->position ?? '—' }}</p>
            </div>
        </div>
    </div>

    <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-bold text-gray-600">DATA PEKERJAAN</p>
        <dl class="mt-2 space-y-3 text-sm">
            <div class="flex justify-between">
                <dt class="text-gray-500">Departemen</dt>
                <dd class="font-semibold text-gray-800">{{ $employee->department ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Tanggal Bergabung</dt>
                <dd class="font-semibold text-gray-800">{{ $employee->join_date?->isoFormat('D MMMM Y') ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">No. HP</dt>
                <dd class="font-semibold text-gray-800">{{ $employee->phone ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Status</dt>
                <dd>
                    @if ($employee->isActive())
                        <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Aktif</span>
                    @else
                        <span class="rounded-full bg-gray-200 px-2.5 py-1 text-xs font-semibold text-gray-600">Nonaktif</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-bold text-gray-600">AKUN</p>

        <form method="POST" action="{{ route('employee.profile.update') }}" class="mt-3 space-y-3">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="text-sm font-semibold text-gray-700">Nama</label>
                <input id="name" type="text" name="name" value="{{ old('name', $employee->user->name) }}" required
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="text-sm font-semibold text-gray-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $employee->user->email) }}" required
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <hr class="border-gray-100">

            <div>
                <label for="current_password" class="text-sm font-semibold text-gray-700">Password Lama</label>
                <input id="current_password" type="password" name="current_password" autocomplete="current-password"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                @error('current_password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label for="password" class="text-sm font-semibold text-gray-700">Password Baru</label>
                    <input id="password" type="password" name="password" autocomplete="new-password"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="text-sm font-semibold text-gray-700">Konfirmasi</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <button type="submit"
                class="w-full rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white transition active:scale-[0.98]">
                Simpan Perubahan
            </button>
        </form>
    </div>
@endsection