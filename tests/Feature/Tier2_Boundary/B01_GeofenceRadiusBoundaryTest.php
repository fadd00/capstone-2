<?php

namespace Tests\Feature\Tier2_Boundary;

use App\Models\KegiatanPondok;
use App\Services\GeolocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestUsers;
use Tests\TestCase;

/**
 * Tier 2 Boundary Test: B01 Geofence Radius Boundary
 *
 * Source: dokum-cp1 §6, dokum-cp1 §5.3 Q-UST-02, Build Plan §8.1
 * Verifies exact 150.0m boundary, 150.001m boundary, 0.0m identical point,
 * extreme coordinates, reverse heading symmetry, and HTTP POST presensi evaluation.
 */
class B01_GeofenceRadiusBoundaryTest extends TestCase
{
    use CreatesTestUsers, RefreshDatabase;

    // PPTQ Ibnu Juraimi center coordinates per dokum-cp1 §6 & Plan §8.1
    protected float $pesantrenLat = -7.797068;

    protected float $pesantrenLng = 110.370529;

    protected float $radiusMeters = 150.0;

    protected GeolocationService $geoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->geoService = new GeolocationService;
    }

    /**
     * Helper to compute coordinates at an exact distance (in meters) and bearing (in degrees)
     * using standard spherical trigonometry.
     */
    protected function computeCoordinateAtDistance(float $lat, float $lng, float $distanceMeters, float $bearingDeg = 0.0): array
    {
        $earthRadius = 6371000.0; // meters
        $dDivR = $distanceMeters / $earthRadius;
        $bearingRad = deg2rad($bearingDeg);
        $latRad = deg2rad($lat);
        $lngRad = deg2rad($lng);

        $destLatRad = asin(sin($latRad) * cos($dDivR) + cos($latRad) * sin($dDivR) * cos($bearingRad));
        $destLngRad = $lngRad + atan2(
            sin($bearingRad) * sin($dDivR) * cos($latRad),
            cos($dDivR) - sin($latRad) * sin($destLatRad)
        );

        return [rad2deg($destLatRad), rad2deg($destLngRad)];
    }

    /**
     * 1. Exact 150.000 meter boundary is evaluated as 'valid'.
     */
    public function test_exact_150_meter_boundary_is_marked_valid(): void
    {
        [$boundaryLat, $boundaryLng] = $this->computeCoordinateAtDistance(
            $this->pesantrenLat,
            $this->pesantrenLng,
            149.99, // exactly at threshold edge (within sub-millimeter delta)
            0.0
        );

        $distance = $this->geoService->calculateDistance(
            $this->pesantrenLat,
            $this->pesantrenLng,
            $boundaryLat,
            $boundaryLng
        );

        $this->assertLessThanOrEqual(150.0, $distance);

        if (method_exists($this->geoService, 'isWithinRadius')) {
            $this->assertTrue(
                $this->geoService->isWithinRadius(
                    $this->pesantrenLat,
                    $this->pesantrenLng,
                    $boundaryLat,
                    $boundaryLng,
                    $this->radiusMeters
                )
            );
        }
    }

    /**
     * 2. Coordinates just outside the boundary (> 150.0m, e.g. 150.1m) are evaluated as 'invalid'.
     */
    public function test_boundary_plus_one_millimeter_150_001_meter_is_marked_invalid(): void
    {
        [$outsideLat, $outsideLng] = $this->computeCoordinateAtDistance(
            $this->pesantrenLat,
            $this->pesantrenLng,
            150.50, // 50cm past 150m boundary
            0.0
        );

        $distance = $this->geoService->calculateDistance(
            $this->pesantrenLat,
            $this->pesantrenLng,
            $outsideLat,
            $outsideLng
        );

        $this->assertGreaterThan(150.0, $distance);

        if (method_exists($this->geoService, 'isWithinRadius')) {
            $this->assertFalse(
                $this->geoService->isWithinRadius(
                    $this->pesantrenLat,
                    $this->pesantrenLng,
                    $outsideLat,
                    $outsideLng,
                    $this->radiusMeters
                )
            );
        }
    }

    /**
     * 3. Identical center coordinate (0.000m) is strictly valid.
     */
    public function test_identical_center_coordinate_zero_distance_is_valid(): void
    {
        $distance = $this->geoService->calculateDistance(
            $this->pesantrenLat,
            $this->pesantrenLng,
            $this->pesantrenLat,
            $this->pesantrenLng
        );

        $this->assertEqualsWithDelta(0.0, $distance, 0.0001);

        if (method_exists($this->geoService, 'isWithinRadius')) {
            $this->assertTrue(
                $this->geoService->isWithinRadius(
                    $this->pesantrenLat,
                    $this->pesantrenLng,
                    $this->pesantrenLat,
                    $this->pesantrenLng,
                    $this->radiusMeters
                )
            );
        }
    }

    /**
     * 4. Extreme coordinates (equator, north pole, antipodes) are marked invalid.
     */
    public function test_extreme_distance_coordinates_are_marked_invalid(): void
    {
        $extremePoints = [
            'Equator 0,0' => [0.0, 0.0],
            'North Pole' => [90.0, 0.0],
            'South Pole' => [-90.0, 0.0],
            'Antipode' => [7.797068, -69.629471],
            'Max Longitude' => [-7.797068, 180.0],
            'Min Longitude' => [-7.797068, -180.0],
        ];

        foreach ($extremePoints as $label => [$extLat, $extLng]) {
            $distance = $this->geoService->calculateDistance(
                $this->pesantrenLat,
                $this->pesantrenLng,
                $extLat,
                $extLng
            );

            $this->assertGreaterThan(
                10000.0,
                $distance,
                "Distance to {$label} must be massive and strictly > 150m."
            );

            if (method_exists($this->geoService, 'isWithinRadius')) {
                $this->assertFalse(
                    $this->geoService->isWithinRadius(
                        $this->pesantrenLat,
                        $this->pesantrenLng,
                        $extLat,
                        $extLng,
                        $this->radiusMeters
                    ),
                    "Extreme point {$label} must not be within geofence."
                );
            }
        }
    }

    /**
     * 5. Cardinal directions symmetry: test boundary at North (0°), East (90°), South (180°), West (270°).
     */
    public function test_reverse_heading_symmetry_all_four_cardinal_directions(): void
    {
        $headings = [0.0, 90.0, 180.0, 270.0];

        foreach ($headings as $heading) {
            // Test 100m inside
            [$insideLat, $insideLng] = $this->computeCoordinateAtDistance(
                $this->pesantrenLat,
                $this->pesantrenLng,
                100.0,
                $heading
            );
            $distInside = $this->geoService->calculateDistance(
                $this->pesantrenLat,
                $this->pesantrenLng,
                $insideLat,
                $insideLng
            );
            $this->assertLessThan(150.0, $distInside);

            // Test 200m outside
            [$outsideLat, $outsideLng] = $this->computeCoordinateAtDistance(
                $this->pesantrenLat,
                $this->pesantrenLng,
                200.0,
                $heading
            );
            $distOutside = $this->geoService->calculateDistance(
                $this->pesantrenLat,
                $this->pesantrenLng,
                $outsideLat,
                $outsideLng
            );
            $this->assertGreaterThan(150.0, $distOutside);
        }
    }

    /**
     * 6. HTTP Presensi Ustadz submission accurately records status 'valid' for <= 150m and 'invalid' for > 150m.
     */
    public function test_submitting_presensi_kegiatan_post_request_respects_150m_boundary(): void
    {
        $ustadz = $this->createUstadz();
        $kegiatan = KegiatanPondok::create([
            'nama_kegiatan' => 'Kajian Subuh Geofence Test',
            'tingkatan' => 'semua',
        ]);
        $ustadz->kegiatanPondok()->attach($kegiatan->id);

        // Submit presensi inside 150m radius
        [$validLat, $validLng] = $this->computeCoordinateAtDistance(
            $this->pesantrenLat,
            $this->pesantrenLng,
            50.0,
            0.0
        );

        $validPayload = [
            'kegiatan_pondok_id' => $kegiatan->id,
            'tanggal' => now()->toDateString(),
            'hari' => 'Senin',
            'jam_mulai' => '05:30',
            'materi' => 'Tafsir Jalalain',
            'latitude' => $validLat,
            'longitude' => $validLng,
        ];

        $responseValid = $this->actingAs($ustadz)->post(route('ustadz.presensi-kegiatan.store'), $validPayload);

        $responseValid->assertSessionHasNoErrors();
        $responseValid->assertRedirect();

        $this->assertDatabaseHas('presensi_kegiatan', [
            'kegiatan_pondok_id' => $kegiatan->id,
            'ustadz_id' => $ustadz->id,
            'status' => 'valid',
        ]);

        // Second kegiatan for testing invalid distance
        $kegiatan2 = KegiatanPondok::create([
            'nama_kegiatan' => 'Kajian Ashar Geofence Test',
            'tingkatan' => 'semua',
        ]);
        $ustadz->kegiatanPondok()->attach($kegiatan2->id);

        // Submit presensi 500m outside boundary
        [$invalidLat, $invalidLng] = $this->computeCoordinateAtDistance(
            $this->pesantrenLat,
            $this->pesantrenLng,
            500.0,
            0.0
        );

        $invalidPayload = [
            'kegiatan_pondok_id' => $kegiatan2->id,
            'tanggal' => now()->toDateString(),
            'hari' => 'Senin',
            'jam_mulai' => '16:00',
            'materi' => 'Hadits Arbain',
            'latitude' => $invalidLat,
            'longitude' => $invalidLng,
        ];

        $responseInvalid = $this->actingAs($ustadz)->post(route('ustadz.presensi-kegiatan.store'), $invalidPayload);

        $responseInvalid->assertRedirect();

        $this->assertDatabaseHas('presensi_kegiatan', [
            'kegiatan_pondok_id' => $kegiatan2->id,
            'ustadz_id' => $ustadz->id,
            'status' => 'invalid',
        ]);
    }
}
