@extends('layouts.employee')

@section('title', 'Absensi')

@section('content')
    {{-- Toast --}}
    <div id="attendance-toast"
        class="mb-3 hidden rounded-xl border px-4 py-3 text-sm font-semibold"
        role="alert"></div>

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <h1 class="text-lg font-bold text-gray-800">Scan QR Absensi</h1>
        <p class="mt-0.5 text-sm text-gray-500">{{ $todayNice }}</p>

        {{-- Loading overlay --}}
        <div id="attendance-overlay" class="hidden">
            <div class="fixed inset-0 z-50 flex flex-col items-center justify-center gap-3 bg-blue-950/80 px-6 text-white backdrop-blur-sm">
                <div class="h-10 w-10 animate-spin rounded-full border-4 border-white/30 border-t-white"></div>
                <p id="attendance-overlay-text" class="text-center text-sm font-semibold">Memproses…</p>
            </div>
        </div>

        <div class="mt-4">
            {{-- Status today --}}
            <div class="rounded-xl bg-gray-50 p-3 text-sm">
                @if ($state['status'] === 'belum')
                    <p class="text-gray-600">Anda <span class="font-bold text-blue-600">belum absen</span> hari ini. Silakan <span class="font-bold">ABSEN MASUK</span>.</p>
                @elseif ($state['status'] === 'hadir')
                    <p class="text-gray-600">Anda sudah <span class="font-bold text-green-600">check-in</span> ({{ $state['label'] }}). Silakan <span class="font-bold text-indigo-600">ABSEN PULANG</span>.</p>
                @else
                    <p class="text-gray-600">Absensi hari ini <span class="font-bold text-indigo-600">sudah selesai</span>.</p>
                @endif
            </div>
        </div>

        {{-- Camera area --}}
        <div class="mt-4">
            <div id="attendance-scanner-area"
                class="aspect-square w-full overflow-hidden rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50"></div>

            <p id="attendance-location-status" class="mt-2 text-center text-xs text-gray-500">Arahkan kamera ke kode QR di lokasi kantor.</p>
            <p id="attendance-scan-status" class="mt-1 text-center text-xs text-gray-600"></p>
        </div>

        {{-- Camera control --}}
        <button id="attendance-start-camera" type="button"
            class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white transition active:scale-[0.98]">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
            </svg>
            Nyalakan Kamera & Scan QR
        </button>

        {{-- Manual token entry --}}
        <div id="attendance-manual-form" class="mt-3 hidden">
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                <p class="text-xs font-bold text-gray-600">KAMERA TIDAK TERSEDIA? Masukkan kode QR manual:</p>
                <div class="mt-2 flex gap-2">
                    <input id="attendance-manual-token" type="text" placeholder="Tempel kode / URL QR di sini" autocomplete="off"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <p class="mt-2 text-xs text-gray-500">Setelah memasukkan kode, paste lalu klik tombol konfirmasi manual di bawah.</p>
            </div>
        </div>

        {{-- Hidden form --}}
        <form id="attendance-form" data-submit="{{ $submitRoute }}"
            class="mt-3 hidden">
            <input type="hidden" id="attendance-token" name="token">
            <input type="hidden" id="attendance-latitude" name="latitude">
            <input type="hidden" id="attendance-longitude" name="longitude">

            <p class="mb-3 flex items-center justify-between text-xs text-gray-500">
                <span id="attendance-accuracy"></span>
                <span>Lokasi dikirim ke server untuk validasi radius.</span>
            </p>

            <button id="attendance-submit" type="submit" disabled
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-green-600 px-4 py-3 text-sm font-bold text-white transition active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-gray-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Menunggu QR & Lokasi…
            </button>
        </form>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/attendance.js'])
@endpush