@extends('layouts.admin')

@section('title', 'Edit Lokasi Absensi')
@section('header', 'Edit Lokasi Absensi')
@section('subheader', $location->name)

@section('content')
    <form method="POST" action="{{ route('admin.locations.update', $location) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
            <div class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-3">
                <div>
                    <label for="name" class="mb-1 block text-sm font-semibold text-gray-700">Nama Lokasi *</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $location->name) }}" required
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('name')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="address" class="mb-1 block text-sm font-semibold text-gray-700">Alamat *</label>
                    <textarea id="address" name="address" rows="2" required
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">{{ old('address', $location->address) }}</textarea>
                    <p id="address-geocode-note" class="mt-1 text-xs text-gray-400">Koordinat menjadi sumber utama radius. Alamat hanya informasi tampilan dan dapat diisi manual.</p>
                    @error('address')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                </div>

                <hr class="border-gray-100">

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="latitude" class="mb-1 block text-sm font-semibold text-gray-700">Latitude *</label>
                        <input id="latitude" name="latitude" type="text" readonly value="{{ old('latitude', $location->latitude) }}" required
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 font-mono text-sm text-gray-700 outline-none"
                            title="Koordinat dikendalikan oleh peta (geser marker / klik peta)">
                        <p class="mt-1 text-xs text-gray-400">Otomatis dari marker / deteksi GPS.</p>
                        @error('latitude')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="longitude" class="mb-1 block text-sm font-semibold text-gray-700">Longitude *</label>
                        <input id="longitude" name="longitude" type="text" readonly value="{{ old('longitude', $location->longitude) }}" required
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 font-mono text-sm text-gray-700 outline-none"
                            title="Koordinat dikendalikan oleh peta (geser marker / klik peta)">
                        <p class="mt-1 text-xs text-gray-400">Otomatis dari marker / deteksi GPS.</p>
                        @error('longitude')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="radius" class="mb-1 block text-sm font-semibold text-gray-700">Radius (meter) *</label>
                        <input id="radius" name="radius" type="number" min="1" step="1" value="{{ old('radius', $location->radius) }}" required
                            class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                        <p class="mt-1 text-xs text-gray-400">Lingkaran di peta ikut berubah sesuai nilai ini.</p>
                        @error('radius')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="is_active" class="mb-1 block text-sm font-semibold text-gray-700">Status</label>
                        <select id="is_active" name="is_active"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                            <option value="1" {{ old('is_active', $location->is_active) ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ ! old('is_active', $location->is_active) ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        @error('is_active')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-xs text-blue-700">
                    Token QR <strong>tidak berubah</strong> saat koordinat dipindahkan. QR baru hanya dibuat lewat tombol "Generate QR Baru".
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="lg:sticky lg:top-24">
                    @include('admin.locations._map', [
                        'mapLat' => old('latitude', $location->latitude),
                        'mapLng' => old('longitude', $location->longitude),
                        'mapRadius' => old('radius', $location->radius),
                        'readOnlyMap' => false,
                    ])
                </div>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('admin.locations.show', $location) }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">Batal</a>
            <button type="submit" class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">Simpan Perubahan</button>
        </div>
    </form>
@endsection

@push('scripts')
    @vite(['resources/js/location-map.js'])
@endpush