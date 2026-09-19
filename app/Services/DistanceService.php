<?php

namespace App\Services;

class DistanceService
{
    /**
     * Radius Bumi dalam meter.
     */
    private const EARTH_RADIUS_METERS = 6371000.0;

    /**
     * Menghitung jarak (meter) antara dua koordinat menggunakan rumus Haversine.
     */
    public function haversine(?float $lat1, ?float $lng1, ?float $lat2, ?float $lng2): ?float
    {
        if ($lat1 === null || $lng1 === null || $lat2 === null || $lng2 === null) {
            return null;
        }

        $originLat = deg2rad((float) $lat1);
        $destinationLat = deg2rad((float) $lat2);
        $deltaLat = deg2rad((float) $lat2 - (float) $lat1);
        $deltaLng = deg2rad((float) $lng2 - (float) $lng1);

        $a = sin($deltaLat / 2) ** 2
            + cos($originLat) * cos($destinationLat) * sin($deltaLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_METERS * $c;
    }
}