<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceLocationRequest;
use App\Models\AttendanceLocation;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceLocationController extends Controller
{
    public function index(): View
    {
        $locations = AttendanceLocation::latest()->paginate(10);

        return view('admin.locations.index', compact('locations'));
    }

    public function create(): View
    {
        return view('admin.locations.create');
    }

    public function store(AttendanceLocationRequest $request): RedirectResponse
    {
        $location = AttendanceLocation::create([
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'radius' => $request->input('radius'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.locations.show', $location)
            ->with('success', 'Lokasi absensi berhasil ditambahkan dengan QR Code baru.');
    }

    public function show(AttendanceLocation $location): View
    {
        return view('admin.locations.show', compact('location'));
    }

    public function edit(AttendanceLocation $location): View
    {
        return view('admin.locations.edit', compact('location'));
    }

    public function update(AttendanceLocationRequest $request, AttendanceLocation $location): RedirectResponse
    {
        $location->update([
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'radius' => $request->input('radius'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.locations.show', $location)
            ->with('success', 'Lokasi absensi berhasil diperbarui.');
    }

    public function toggleStatus(AttendanceLocation $location): RedirectResponse
    {
        $location->is_active = ! $location->is_active;
        $location->save();

        $message = $location->is_active
            ? 'Lokasi berhasil diaktifkan. QR Code dapat digunakan kembali.'
            : 'Lokasi berhasil dinonaktifkan. QR Code tidak akan berlaku untuk absensi.';

        return back()->with('success', $message);
    }

    public function qr(AttendanceLocation $location, QrCodeService $qrCodeService): View
    {
        $qrSvg = $qrCodeService->svg($location->getQrUrl(), 320);

        return view('admin.locations.qr', compact('location', 'qrSvg'));
    }

    public function generateQr(AttendanceLocation $location): RedirectResponse
    {
        $location->regenerateToken()->save();

        return redirect()
            ->route('admin.locations.qr', $location)
            ->with('success', 'QR Code baru berhasil dibuat. Token lama tidak berlaku lagi!');
    }
}