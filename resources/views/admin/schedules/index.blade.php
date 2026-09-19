@extends('layouts.admin')

@section('title', 'Jadwal Kerja')
@section('header', 'Jadwal Kerja')
@section('subheader', 'Kelola jadwal kerja karyawan')

@section('content')
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-bold text-gray-800">Semua Jadwal</h2>
                <p class="text-sm text-gray-500">Total: {{ $schedules->total() }} jadwal</p>
            </div>
            <a href="{{ route('admin.schedules.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Tambah Jadwal
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Nama Jadwal</th>
                        <th class="px-5 py-3 text-left">Hari</th>
                        <th class="px-5 py-3 text-left">Jam Mulai</th>
                        <th class="px-5 py-3 text-left">Jam Selesai</th>
                        <th class="px-5 py-3 text-left">Toleransi</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($schedules as $schedule)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-5 py-4 font-semibold text-gray-900">{{ $schedule->name }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $schedule->day }}</td>
                            <td class="px-5 py-4 font-mono text-gray-700">{{ \Illuminate\Support\Carbon::parse($schedule->start_time)->format('H:i') }}</td>
                            <td class="px-5 py-4 font-mono text-gray-700">{{ \Illuminate\Support\Carbon::parse($schedule->end_time)->format('H:i') }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $schedule->late_tolerance }} menit</td>
                            <td class="px-5 py-4">
                                @if ($schedule->is_active)
                                    <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Aktif</span>
                                @else
                                    <span class="rounded-full bg-gray-200 px-2.5 py-1 text-xs font-semibold text-gray-600">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.schedules.edit', $schedule) }}" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">Edit</a>
                                    <form method="POST" action="{{ route('admin.schedules.toggle', $schedule) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ $schedule->is_active ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}">
                                            {{ $schedule->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}"
                                        onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-100">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16">
                                <div class="text-center">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                                        </svg>
                                    </div>
                                    <p class="mt-3 font-semibold text-gray-700">Belum ada jadwal kerja</p>
                                    <p class="mt-1 text-sm text-gray-500">Tambahkan jadwal kerja pertama Anda.</p>
                                    <a href="{{ route('admin.schedules.create') }}" class="mt-4 inline-block rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Tambah Jadwal</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($schedules->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>
@endsection