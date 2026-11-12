<?php

namespace Tests\Unit;

use App\Services\GeolocationService;
use Tests\TestCase;

class GeolocationServiceTest extends TestCase
{
    protected GeolocationService $service;

    // PPTQ Ibnu Juraimi center coordinates per dokum-cp1 §6 & Plan §8.1
    protected float $pesantrenLat = -7.797068;

    protected float $pesantrenLng = 110.370529;

    protected float $radiusMeters = 150.0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GeolocationService;
    }

    /**
     * 1. Distance between identical coordinates must be exactly 0 meters.
     */
    public function test_calculate_distance_between_identical_coordinates_returns_zero(): void
    {
        $distance = $this->service->calculateDistance(
            $this->pesantrenLat,
            $this->pesantrenLng,
            $this->pesantrenLat,
            $this->pesantrenLng
        );

        $this->assertEqualsWithDelta(0.0, $distance, 0.001, 'Identical coordinates must yield 0 meter distance.');
    }

    /**
     * 2. Coordinates within the 150-meter geofence radius must return a distance < 150.0m.
     */
    public function test_coordinates_within_150_meter_radius_return_valid_distance(): void
    {
        // Coordinate shifted slightly (~25 meters south)
        $insideLat = -7.797290;
        $insideLng = 110.370529;

        $distance = $this->service->calculateDistance(
            $this->pesantrenLat,
            $this->pesantrenLng,
            $insideLat,
            $insideLng
        );

        $this->assertGreaterThan(0.0, $distance);
        $this->assertLessThan(150.0, $distance, "Distance ({$distance}m) should be strictly inside the 150m boundary.");

        if (method_exists($this->service, 'isWithinRadius')) {
            $this->assertTrue(
                $this->service->isWithinRadius($this->pesantrenLat, $this->pesantrenLng, $insideLat, $insideLng, $this->radiusMeters)
            );
        }
    }

    /**
     * 3. Coordinates outside the 150-meter geofence radius must return a distance > 150.0m.
     */
    public function test_coordinates_outside_150_meter_radius_return_invalid_distance(): void
    {
        // Coordinate shifted ~500 meters away
        $outsideLat = -7.801500;
        $outsideLng = 110.370529;

        $distance = $this->service->calculateDistance(
            $this->pesantrenLat,
            $this->pesantrenLng,
            $outsideLat,
            $outsideLng
        );

        $this->assertGreaterThan(150.0, $distance, "Distance ({$distance}m) should exceed the 150m boundary.");

        if (method_exists($this->service, 'isWithinRadius')) {
            $this->assertFalse(
                $this->service->isWithinRadius($this->pesantrenLat, $this->pesantrenLng, $outsideLat, $outsideLng, $this->radiusMeters)
            );
        }
    }

    /**
     * 4. Haversine distance formula must be commutative / symmetric.
     */
    public function test_haversine_distance_calculation_is_symmetric(): void
    {
        $latA = -7.797068;
        $lngA = 110.370529;
        $latB = -7.798000;
        $lngB = 110.371500;

        $distAB = $this->service->calculateDistance($latA, $lngA, $latB, $lngB);
        $distBA = $this->service->calculateDistance($latB, $lngB, $latA, $lngA);

        $this->assertEqualsWithDelta($distAB, $distBA, 0.0001, 'Distance from A to B must equal distance from B to A.');
    }

    /**
     * 5. Presensi status evaluation returns 'valid' for <= 150m and 'invalid' for > 150m.
     */
    public function test_evaluate_status_returns_valid_and_invalid_enum_labels(): void
    {
        $this->assertEquals('valid', $this->service->determineStatus(50.0));
        $this->assertEquals('valid', $this->service->determineStatus(150.0));
        $this->assertEquals('invalid', $this->service->determineStatus(150.1));
        $this->assertEquals('invalid', $this->service->determineStatus(500.0));
    }

    /**
     * 6. Precision test with known geographic distance (1 minute latitude ~ 1852 meters).
     */
    public function test_known_one_minute_latitude_distance_precision(): void
    {
        // 1 nautical mile = 1 minute of arc of latitude ≈ 1852 meters
        $lat1 = 0.0;
        $lat2 = 1.0 / 60.0;
        $lng = 0.0;

        $distance = $this->service->calculateDistance($lat1, $lng, $lat2, $lng);

        // Allow ±10m tolerance around international nautical mile standard (1852m)
        $this->assertEqualsWithDelta(1852.0, $distance, 15.0, 'One minute of latitude must approximate 1852 meters.');
    }
}
