<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLocation;
use Illuminate\View\View;

class AbsensiScanController extends Controller
{
    /**
     * Public location information page reached via QR token.
     * GPS / check-in / check-out will be implemented in Bagian 2.
     */
    public function show(string $publicToken): View
    {
        $location = AttendanceLocation::where('public_token', $publicToken)->first();

        if (! $location) {
            abort(404, 'QR Code tidak valid atau sudah tidak berlaku.');
        }

        return view('absensi.scan', ['location' => $location]);
    }
}