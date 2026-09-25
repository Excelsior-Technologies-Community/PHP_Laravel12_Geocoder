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
     * Main location page
     *
     * Features:
     * - Search
     * - Coordinate filters
     * - Date filters
     * - Sorting
     * - Pagination
     * - Per-page selection
     * - Bulk selection
     *
     * Default sorting:
     * ID ASC
     */
    public function index(Request $request)
    {
        $query = Location::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(
                'address',
                'like',
                '%' . $search . '%'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Latitude filters
        |--------------------------------------------------------------------------
        */

        if ($request->filled('latitude_min')) {
            $query->where(
                'latitude',
                '>=',
                $request->latitude_min
            );
        }

        if ($request->filled('latitude_max')) {
            $query->where(
                'latitude',
                '<=',
                $request->latitude_max
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Longitude filters
        |--------------------------------------------------------------------------
        */

        if ($request->filled('longitude_min')) {
            $query->where(
                'longitude',
                '>=',
                $request->longitude_min
            );
        }

        if ($request->filled('longitude_max')) {
            $query->where(
                'longitude',
                '<=',
                $request->longitude_max
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date filters
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->date_from
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->date_to
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'id',
            'address',
            'latitude',
            'longitude',
            'created_at',
        ];

        /*
        | Default = ID
        */
        $sort = $request->get('sort', 'id');

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }

        /*
        | Default = ASC
        */
        $direction = $request->get('direction', 'asc');

        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $query->orderBy($sort, $direction);

        /*
        |--------------------------------------------------------------------------
        | Per page
        |--------------------------------------------------------------------------
        */

        $allowedPerPage = [
            5,
            10,
            25,
            50,
        ];

        $perPage = (int) $request->get(
            'per_page',
            5
        );

        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 5;
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $locations = $query
            ->paginate($perPage)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Map locations
        |--------------------------------------------------------------------------
        */

        $mapLocations = Location::orderBy(
            'id',
            'asc'
        )->get();

        return view(
            'location',
            compact(
                'locations',
                'mapLocations',
                'sort',
                'direction',
                'perPage'
            )
        );
    }

    /**
     * Store and geocode a new address.
     *
     * Includes duplicate prevention.
     */
    public function store(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:255',
        ]);

        $address = trim(
            $request->address
        );

        /*
        |--------------------------------------------------------------------------
        | Duplicate address prevention
        |--------------------------------------------------------------------------
        */

        $duplicate = Location::whereRaw(
            'LOWER(address) = ?',
            [strtolower($address)]
        )->first();

        if ($duplicate) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'This address already exists in your saved locations.'
                );
        }

        try {
            /*
            |--------------------------------------------------------------------------
            | Geocode address
            |--------------------------------------------------------------------------
            */

            $result = $this->geo->geocode(
                $address
            );

            if (!$result) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Location not found.'
                    );
            }

            $coordinates = $result->getCoordinates();

            /*
            |--------------------------------------------------------------------------
            | Save location
            |--------------------------------------------------------------------------
            */

            Location::create([
                'address' => $address,

                'latitude' =>
                    $coordinates->getLatitude(),

                'longitude' =>
                    $coordinates->getLongitude(),
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
     * Show edit page.
     */
    public function edit(Location $location)
    {
        return view(
            'locations.edit',
            compact('location')
        );
    }

    /**
     * Update address and re-geocode.
     */
    public function update(
        Request $request,
        Location $location
    ) {
        $request->validate([
            'address' => 'required|string|max:255',
        ]);

        $address = trim(
            $request->address
        );

        /*
        |--------------------------------------------------------------------------
        | Duplicate prevention
        |--------------------------------------------------------------------------
        */

        $duplicate = Location::whereRaw(
            'LOWER(address) = ?',
            [strtolower($address)]
        )
            ->where(
                'id',
                '!=',
                $location->id
            )
            ->first();

        if ($duplicate) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Another location already uses this address.'
                );
        }

        try {

            /*
            |--------------------------------------------------------------------------
            | Re-geocode updated address
            |--------------------------------------------------------------------------
            */

            $result = $this->geo->geocode(
                $address
            );

            if (!$result) {
                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Updated address could not be geocoded.'
                    );
            }

            $coordinates = $result->getCoordinates();

            /*
            |--------------------------------------------------------------------------
            | Update
            |--------------------------------------------------------------------------
            */

            $location->update([
                'address' => $address,

                'latitude' =>
                    $coordinates->getLatitude(),

                'longitude' =>
                    $coordinates->getLongitude(),
            ]);

            return redirect()
                ->route(
                    'location.show',
                    $location
                )
                ->with(
                    'success',
                    'Location updated and re-geocoded successfully!'
                );

        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Unable to update and geocode this address.'
                );
        }
    }

    /**
     * Delete one location.
     */
    public function destroy(Location $location)
    {
        $location->delete();

        return redirect()
            ->route('location.index')
            ->with(
                'success',
                'Location deleted successfully.'
            );
    }

    /**
     * Bulk delete locations.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'location_ids' =>
                'required|array|min:1',

            'location_ids.*' =>
                'integer|exists:locations,id',
        ]);

        $count = Location::whereIn(
            'id',
            $request->location_ids
        )->delete();

        return redirect()
            ->route('location.index')
            ->with(
                'success',
                $count . ' location(s) deleted successfully.'
            );
    }

    /**
     * Individual location details.
     */
    public function show(Location $location)
    {
        return view(
            'locations.show',
            compact('location')
        );
    }

    /**
     * Analytics dashboard.
     */
    public function dashboard()
    {
        /*
        |--------------------------------------------------------------------------
        | Basic statistics
        |--------------------------------------------------------------------------
        */

        $totalLocations = Location::count();

        $locationsToday = Location::whereDate(
            'created_at',
            today()
        )->count();

        $uniqueAddresses = Location::distinct(
            'address'
        )->count('address');

        $averageLatitude = Location::avg(
            'latitude'
        );

        $averageLongitude = Location::avg(
            'longitude'
        );

        /*
        |--------------------------------------------------------------------------
        | Latest locations
        |--------------------------------------------------------------------------
        |
        | ID ASC
        |
        */

        $latestLocations = Location::orderBy(
            'id',
            'asc'
        )
            ->take(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | First location
        |--------------------------------------------------------------------------
        */

        $firstLocation = Location::orderBy(
            'id',
            'asc'
        )->first();

        /*
        |--------------------------------------------------------------------------
        | Latest location
        |--------------------------------------------------------------------------
        */

        $latestLocation = Location::orderBy(
            'id',
            'desc'
        )->first();

        /*
        |--------------------------------------------------------------------------
        | Additional statistics
        |--------------------------------------------------------------------------
        */

        $northLocations = Location::where(
            'latitude',
            '>=',
            0
        )->count();

        $southLocations = Location::where(
            'latitude',
            '<',
            0
        )->count();

        $eastLocations = Location::where(
            'longitude',
            '>=',
            0
        )->count();

        $westLocations = Location::where(
            'longitude',
            '<',
            0
        )->count();

        return view(
            'locations.dashboard',
            compact(
                'totalLocations',
                'locationsToday',
                'uniqueAddresses',
                'averageLatitude',
                'averageLongitude',
                'latestLocations',
                'firstLocation',
                'latestLocation',
                'northLocations',
                'southLocations',
                'eastLocations',
                'westLocations'
            )
        );
    }

    /**
     * Distance calculator page.
     */
    public function distance()
    {
        $locations = Location::orderBy(
            'id',
            'asc'
        )->get();

        return view(
            'locations.distance',
            compact('locations')
        );
    }

    /**
     * Calculate distance.
     */
    public function calculateDistance(
        Request $request
    ) {
        $request->validate([
            'location_one' =>
                'required|different:location_two|exists:locations,id',

            'location_two' =>
                'required|exists:locations,id',
        ]);

        $locationOne = Location::findOrFail(
            $request->location_one
        );

        $locationTwo = Location::findOrFail(
            $request->location_two
        );

        $distance = $this->calculateDistanceInKm(
            $locationOne->latitude,
            $locationOne->longitude,
            $locationTwo->latitude,
            $locationTwo->longitude
        );

        $locations = Location::orderBy(
            'id',
            'asc'
        )->get();

        return view(
            'locations.distance',
            compact(
                'locations',
                'locationOne',
                'locationTwo',
                'distance'
            )
        );
    }

    /**
     * Haversine distance.
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
     * Export filtered locations as CSV.
     *
     * Default export order:
     * ID ASC
     */
    public function export(Request $request)
    {
        $query = $this->buildFilterQuery(
            $request
        );

        $locations = $query
            ->orderBy('id', 'asc')
            ->get();

        $fileName =
            'geocoder-locations-' .
            now()->format('Y-m-d-H-i-s') .
            '.csv';

        $headers = [
            'Content-Type' =>
                'text/csv',

            'Content-Disposition' =>
                'attachment; filename="' .
                $fileName .
                '"',

            'Pragma' =>
                'no-cache',

            'Cache-Control' =>
                'must-revalidate, post-check=0, pre-check=0',

            'Expires' =>
                '0',
        ];

        $callback = function () use ($locations) {

            $file = fopen(
                'php://output',
                'w'
            );

            /*
            |--------------------------------------------------------------------------
            | CSV header
            |--------------------------------------------------------------------------
            */

            fputcsv($file, [
                'ID',
                'Address',
                'Latitude',
                'Longitude',
                'Created At',
                'Updated At',
            ]);

            /*
            |--------------------------------------------------------------------------
            | CSV rows
            |--------------------------------------------------------------------------
            */

            foreach ($locations as $location) {

                fputcsv($file, [
                    $location->id,

                    $location->address,

                    $location->latitude,

                    $location->longitude,

                    $location->created_at
                        ? $location->created_at->format(
                            'Y-m-d H:i:s'
                        )
                        : '',

                    $location->updated_at
                        ? $location->updated_at->format(
                            'Y-m-d H:i:s'
                        )
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

    /**
     * Export locations as JSON.
     *
     * Default order:
     * ID ASC
     */
    public function exportJson(
        Request $request
    ) {
        $query = $this->buildFilterQuery(
            $request
        );

        $locations = $query
            ->orderBy('id', 'asc')
            ->get([
                'id',
                'address',
                'latitude',
                'longitude',
                'created_at',
                'updated_at',
            ]);

        $fileName =
            'geocoder-locations-' .
            now()->format('Y-m-d-H-i-s') .
            '.json';

        return response()->json(
            $locations,
            200,
            [
                'Content-Disposition' =>
                    'attachment; filename="' .
                    $fileName .
                    '"',
            ]
        );
    }

    /**
     * Build common filtering query.
     */
    private function buildFilterQuery(
        Request $request
    ) {
        $query = Location::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $query->where(
                'address',
                'like',
                '%' . $request->search . '%'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Latitude
        |--------------------------------------------------------------------------
        */

        if ($request->filled('latitude_min')) {

            $query->where(
                'latitude',
                '>=',
                $request->latitude_min
            );
        }

        if ($request->filled('latitude_max')) {

            $query->where(
                'latitude',
                '<=',
                $request->latitude_max
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Longitude
        |--------------------------------------------------------------------------
        */

        if ($request->filled('longitude_min')) {

            $query->where(
                'longitude',
                '>=',
                $request->longitude_min
            );
        }

        if ($request->filled('longitude_max')) {

            $query->where(
                'longitude',
                '<=',
                $request->longitude_max
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_from')) {

            $query->whereDate(
                'created_at',
                '>=',
                $request->date_from
            );
        }

        if ($request->filled('date_to')) {

            $query->whereDate(
                'created_at',
                '<=',
                $request->date_to
            );
        }

        return $query;
    }
}