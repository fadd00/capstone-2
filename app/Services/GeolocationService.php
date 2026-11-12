<?php

namespace App\Services;

class GeolocationService
{
    public const PESANTREN_LAT = -7.797068;

    public const PESANTREN_LNG = 110.370529;

    public const DEFAULT_RADIUS_METERS = 150.0;

    public const EARTH_RADIUS_METERS = 6371000.0;

    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        if ($lat1 === $lat2 && $lng1 === $lng2) {
            return 0.0;
        }

        $latFrom = deg2rad($lat1);
        $lngFrom = deg2rad($lng1);
        $latTo = deg2rad($lat2);
        $lngTo = deg2rad($lng2);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $angle = 2 * asin(sqrt(
            sin($latDelta / 2) ** 2 +
            cos($latFrom) * cos($latTo) * (sin($lngDelta / 2) ** 2)
        ));

        return $angle * self::EARTH_RADIUS_METERS;
    }

    public function isWithinRadius(float $lat1, float $lng1, float $lat2, float $lng2, float $radiusMeters = self::DEFAULT_RADIUS_METERS): bool
    {
        return $this->calculateDistance($lat1, $lng1, $lat2, $lng2) <= $radiusMeters;
    }

    public function determineStatus(float $distance, float $radiusMeters = self::DEFAULT_RADIUS_METERS): string
    {
        return $distance <= $radiusMeters ? 'valid' : 'invalid';
    }
}
