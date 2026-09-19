@extends('layouts.admin')

@section('title', 'QR Code — '.$location->name)
@section('header', 'QR Code Lokasi')
@section('subheader', $location->name)

@push('styles')
    <style>
        @media print {
            aside, header, footer, .no-print { display: none !important; }
            main { max-width: 100% !important; padding: 0 !important; }
            .print-card { box-shadow: none !important; border: none !important; }
            body { background: #fff !important; }
        }
    </style>
@endpush

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- QR Card --}}
        <div class="print-card rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
            <div class="flex flex-col gap-4 border-b border-gray-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $location->name }}</h2>
                    <p class="mt-0.5 text-sm text-gray-500">{{ $location->address }}</p>
                </div>
                @if ($location->is_active)
                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Lokasi Aktif</span>
                @else
                    <span class="rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-600">Lokasi Nonaktif</span>
                @endif
            </div>

            <div class="mt-6 flex flex-col items-center">
                <div id="qr-container" class="rounded-2xl border-2 border-dashed border-gray-300 bg-white p-6">
                    <div id="qr-svg" class="qr-svg h-56 w-56 sm:h-72 sm:w-72">{!! $qrSvg !!}</div>
                </div>
                <p class="mt-4 text-sm font-semibold text-gray-700">Scan QR Code untuk membuka halaman absensi</p>
                <p class="mt-1 break-all text-center font-mono text-xs text-gray-500">{{ $location->getQrUrl() }}</p>
                <div class="mt-3 flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-500">
                    <span>Radius:</span><span class="font-semibold text-gray-700">{{ $location->radius }} m</span>
                    <span class="mx-1">•</span>
                    <span>Koordinat:</span><span class="font-mono font-semibold text-gray-700">{{ $location->latitude }}, {{ $location->longitude }}</span>
                </div>
            </div>

            <div class="no-print mt-6 flex flex-wrap items-center justify-center gap-2 border-t border-gray-100 pt-5">
                <button type="button" onclick="openEnlarge()"
                    class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">Perbesar</button>
                <button type="button" onclick="window.print()"
                    class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">Print QR</button>
                <form method="POST" action="{{ route('admin.locations.generate-qr', $location) }}"
                    onsubmit="return confirm('Generate QR baru? Token lama akan segera tidak berlaku dan QR lama tidak dapat digunakan.')">
                    @csrf
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">Generate QR Baru</button>
                </form>
                <form method="POST" action="{{ route('admin.locations.toggle', $location) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="rounded-lg border px-4 py-2.5 text-sm font-semibold transition {{ $location->is_active ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}">
                        {{ $location->is_active ? 'Nonaktifkan Lokasi' : 'Aktifkan Lokasi' }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Info token --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="font-bold text-gray-800">Informasi Token QR</h3>
            </div>
            <div class="space-y-4 p-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Public Token</p>
                    <p class="mt-1 break-all font-mono text-sm text-gray-800">{{ $location->public_token }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Diregulasi</p>
                    <p class="mt-1 text-sm text-gray-700">Token dibuat acak (40 karakter) dan unik. Tidak menggunakan ID database.</p>
                </div>
                <div class="rounded-lg bg-blue-50 p-3 text-xs text-blue-800">
                    <p class="font-semibold">Catatan</p>
                    <p class="mt-1">Saat "Generate QR Baru" dilakukan, token lama otomatis tidak berlaku. Riwayat absensi lama tidak dihapus.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Perbesar --}}
    <div id="enlarge-modal" class="no-print fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/70 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="font-bold text-gray-800">QR Code — {{ $location->name }}</h3>
                <button type="button" onclick="closeEnlarge()" class="rounded p-1 text-gray-500 hover:bg-gray-100">&times;</button>
            </div>
            <div class="flex justify-center rounded-2xl border-2 border-dashed border-gray-300 bg-white p-6">
                <div class="qr-svg h-72 w-72">{!! $qrSvg !!}</div>
            </div>
            <p class="mt-4 break-all text-center font-mono text-xs text-gray-500">{{ $location->getQrUrl() }}</p>
            <button type="button" onclick="window.print(); closeEnlarge();"
                class="mt-4 w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">Print QR</button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openEnlarge() {
            document.getElementById('enlarge-modal').classList.remove('hidden');
            document.getElementById('enlarge-modal').classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeEnlarge() {
            document.getElementById('enlarge-modal').classList.add('hidden');
            document.getElementById('enlarge-modal').classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        document.getElementById('enlarge-modal')?.addEventListener('click', function (e) {
            if (e.target === this) closeEnlarge();
        });
    </script>
@endpush