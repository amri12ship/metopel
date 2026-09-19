@extends('layouts.admin')

@section('title', 'Detail Lokasi')
@section('header', 'Detail Lokasi')
@section('subheader', $location->name)

@section('content')
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-gray-100 p-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $location->name }}</h2>
                <p class="mt-0.5 text-sm text-gray-500">{{ $location->address }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($location->is_active)
                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Aktif</span>
                @else
                    <span class="rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-600">Nonaktif</span>
                @endif
                <a href="{{ route('admin.locations.edit', $location) }}" class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-100">Edit</a>
                <a href="{{ route('admin.locations.qr', $location) }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">Lihat QR</a>
                <form method="POST" action="{{ route('admin.locations.toggle', $location) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="rounded-lg border px-4 py-2 text-sm font-semibold transition {{ $location->is_active ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}">
                        {{ $location->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Latitude</p>
                <p class="mt-1 font-mono font-semibold text-gray-800">{{ $location->latitude }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Longitude</p>
                <p class="mt-1 font-mono font-semibold text-gray-800">{{ $location->longitude }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Radius</p>
                <p class="mt-1 font-semibold text-gray-800">{{ $location->radius }} meter</p>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Public Token (QR)</p>
                <p class="mt-1 break-all font-mono text-sm text-gray-800">{{ $location->public_token }}</p>
                <p class="mt-1 text-xs text-gray-500">Token dibuat acak dan tidak menggunakan ID database.</p>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">URL Scan</p>
                <p class="mt-1 break-all font-mono text-sm text-blue-700">{{ $location->getQrUrl() }}</p>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Peta</p>
                @include('admin.locations._map', [
                    'mapLat' => $location->latitude,
                    'mapLng' => $location->longitude,
                    'mapRadius' => $location->radius,
                    'readOnlyMap' => true,
                ])
            </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-gray-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('admin.locations.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">&larr; Kembali ke daftar</a>
            <form method="POST" action="{{ route('admin.locations.generate-qr', $location) }}"
                onsubmit="return confirm('Generate QR baru? Token lama akan segera tidak berlaku.')">
                @csrf
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">Generate QR Baru</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/location-map.js'])
@endpush