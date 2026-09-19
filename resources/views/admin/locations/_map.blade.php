@php
    $mapLat = $mapLat ?? 3.5952;
    $mapLng = $mapLng ?? 98.6722;
    $mapRadius = $mapRadius ?? 100;
    $readOnlyMap = $readOnlyMap ?? false;
@endphp
<div class="relative overflow-hidden rounded-2xl border border-gray-300">
    <div id="location-map"
        class="h-[400px] w-full"
        data-lat="{{ $mapLat }}"
        data-lng="{{ $mapLng }}"
        data-radius="{{ $mapRadius }}"
        data-readonly="{{ $readOnlyMap ? 'true' : 'false' }}"
        data-zoom="16"></div>

    @if (! $readOnlyMap)
        <div class="absolute left-3 top-3 z-[1000] flex flex-col gap-2">
            <button type="button" id="btn-focus-location" title="Fokus ke marker"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow transition hover:bg-gray-100">
                ⦿ Fokus Lokasi
            </button>
        </div>
    @endif
</div>

@if (! $readOnlyMap)
    <div class="mt-3 flex flex-wrap items-center gap-2">
        <button type="button" id="btn-detect-location"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60">
            📍 Deteksi Lokasi Saya
        </button>
        <button type="button" id="btn-reset-location"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
            ↩ Kembalikan Lokasi Tersimpan
        </button>
    </div>
    <p id="gps-permission-info" class="mt-2 text-xs text-gray-400">
        Untuk menentukan lokasi secara otomatis, browser akan meminta izin mengakses lokasi Anda.
    </p>
    <p id="gps-status" class="mt-1 text-xs text-gray-400"></p>
@endif