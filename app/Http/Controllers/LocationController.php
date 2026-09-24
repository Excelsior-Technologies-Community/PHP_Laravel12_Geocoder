<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Services\GeocoderService;
use Illuminate\Support\Facades\Response;

class LocationController extends Controller
{
    protected $geo;

    public function __construct(GeocoderService $geo)
    {
        $this->geo = $geo;
    }

    /**
     * Display geocoder page with search, filters,
     * pagination and map.
     */
    public function index(Request $request)
    {
        $query = Location::query();

        // Search by address
        if ($request->filled('search')) {
            $query->where('address', 'like', '%' . $request->search . '%');
        }

        // Minimum latitude
        if ($request->filled('latitude_min')) {
            $query->where('latitude', '>=', $request->latitude_min);
        }

        // Maximum latitude
        if ($request->filled('latitude_max')) {
            $query->where('latitude', '<=', $request->latitude_max);
        }

        // Minimum longitude
        if ($request->filled('longitude_min')) {
            $query->where('longitude', '>=', $request->longitude_min);
        }

        // Maximum longitude
        if ($request->filled('longitude_max')) {
            $query->where('longitude', '<=', $request->longitude_max);
        }

        // Date from
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // Date to
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $locations = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // All locations for the map
        $mapLocations = Location::latest()->get();

        return view('location', compact(
            'locations',
            'mapLocations'
        ));
    }

    /**
     * Geocode and save a new address.
     */
    public function store(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:255'
        ]);

        try {
            $result = $this->geo->geocode($request->address);

            if (!$result) {
                return back()
                    ->withInput()
                    ->with('error', 'Location not found.');
            }

            Location::create([
                'address' => $request->address,
                'latitude' => $result->getCoordinates()->getLatitude(),
                'longitude' => $result->getCoordinates()->getLongitude(),
            ]);

            return back()->with(
                'success',
                'Location geocoded and saved successfully!'
            );
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to geocode this address. Please try again.'
                );
        }
    }

    /**
     * Analytics dashboard.
     */
    public function dashboard()
    {
        $totalLocations = Location::count();

        $locationsToday = Location::whereDate(
            'created_at',
            today()
        )->count();

        $uniqueAddresses = Location::distinct('address')->count('address');

        $averageLatitude = Location::avg('latitude');
        $averageLongitude = Location::avg('longitude');

        $latestLocations = Location::latest()
            ->take(10)
            ->get();

        $firstLocation = Location::oldest()->first();
        $latestLocation = Location::latest()->first();

        return view('locations.dashboard', compact(
            'totalLocations',
            'locationsToday',
            'uniqueAddresses',
            'averageLatitude',
            'averageLongitude',
            'latestLocations',
            'firstLocation',
            'latestLocation'
        ));
    }

    /**
     * Display individual location details.
     */
    public function show(Location $location)
    {
        return view('locations.show', compact('location'));
    }

    /**
     * Distance calculator page.
     */
    public function distance()
    {
        $locations = Location::orderBy('address')->get();

        return view('locations.distance', compact('locations'));
    }

    /**
     * Calculate distance between two saved locations.
     */
    public function calculateDistance(Request $request)
    {
        $request->validate([
            'location_one' => 'required|different:location_two|exists:locations,id',
            'location_two' => 'required|exists:locations,id',
        ]);

        $locationOne = Location::findOrFail($request->location_one);
        $locationTwo = Location::findOrFail($request->location_two);

        $distance = $this->calculateDistanceInKm(
            $locationOne->latitude,
            $locationOne->longitude,
            $locationTwo->latitude,
            $locationTwo->longitude
        );

        $locations = Location::orderBy('address')->get();

        return view('locations.distance', compact(
            'locations',
            'locationOne',
            'locationTwo',
            'distance'
        ));
    }

    /**
     * Calculate distance using the Haversine formula.
     */
    private function calculateDistanceInKm(
        $latitude1,
        $longitude1,
        $latitude2,
        $longitude2
    ) {
        $earthRadius = 6371;

        $latitudeDifference = deg2rad(
            $latitude2 - $latitude1
        );

        $longitudeDifference = deg2rad(
            $longitude2 - $longitude1
        );

        $a =
            sin($latitudeDifference / 2) ** 2 +
            cos(deg2rad($latitude1)) *
            cos(deg2rad($latitude2)) *
            sin($longitudeDifference / 2) ** 2;

        $c = 2 * atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

        return $earthRadius * $c;
    }

    /**
     * Export all saved locations as CSV.
     */
    public function export()
    {
        $locations = Location::latest()->get();

        $fileName = 'geocoder-locations-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($locations) {
            $file = fopen('php://output', 'w');

            // CSV header
            fputcsv($file, [
                'ID',
                'Address',
                'Latitude',
                'Longitude',
                'Created At'
            ]);

            foreach ($locations as $location) {
                fputcsv($file, [
                    $location->id,
                    $location->address,
                    $location->latitude,
                    $location->longitude,
                    $location->created_at
                        ? $location->created_at->format('Y-m-d H:i:s')
                        : '',
                ]);
            }

            fclose($file);
        };

        return Response::stream(
            $callback,
            200,
            $headers
        );
    }
}