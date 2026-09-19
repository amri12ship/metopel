<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $location->name }} — Absensi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-blue-950 p-4 font-sans antialiased">
    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 p-6 text-center text-white">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white/15 text-2xl font-bold">A</div>
            <h1 class="mt-3 text-lg font-bold">{{ $location->name }}</h1>
            <p class="mt-1 text-sm text-blue-100">{{ $location->address }}</p>
        </div>

        <div class="space-y-4 p-6">
            @if (! $location->is_active)
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    <p class="font-semibold">Lokasi Nonaktif</p>
                    <p class="mt-1 text-xs">Lokasi absensi ini sedang tidak aktif. QR Code tidak dapat digunakan untuk absensi.</p>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-center">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Radius</p>
                    <p class="mt-1 text-lg font-bold text-gray-800">{{ $location->radius }} meter</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-center">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Status</p>
                    <p class="mt-1 text-lg font-bold {{ $location->is_active ? 'text-green-600' : 'text-red-500' }}">
                        {{ $location->is_active ? 'Aktif' : 'Nonaktif' }}
                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Koordinat Lokasi</p>
                <p class="mt-1 break-all font-mono text-sm text-gray-700">
                    {{ $location->latitude }}, {{ $location->longitude }}
                </p>
            </div>

            <div class="flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-xs text-blue-800">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Halaman ini hanya menampilkan informasi lokasi. Fitur scan, GPS, check-in dan check-out akan tersedia pada tahap berikutnya.</span>
            </div>

            <a href="javascript:window.history.back()"
                class="block w-full rounded-lg border border-gray-300 px-4 py-3 text-center text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                Kembali
            </a>
        </div>
    </div>
</body>
</html>