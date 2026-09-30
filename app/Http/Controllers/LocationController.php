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

        $todayLocations = $locationsToday;

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
                'todayLocations',
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

    /**
     * Reverse Geocoding API Endpoint (for drag-and-drop marker pin)
     */
    public function reverseGeocode(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $lat = (float) $request->lat;
        $lng = (float) $request->lng;

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get('https://nominatim.openstreetmap.org/reverse', [
                'query' => [
                    'format' => 'jsonv2',
                    'lat' => $lat,
                    'lon' => $lng,
                    'addressdetails' => 1,
                ],
                'headers' => [
                    'User-Agent' => 'Laravel12-Geocoder-Studio/1.0',
                ],
                'timeout' => 5,
            ]);

            $data = json_decode($response->getBody(), true);
            $address = $data['display_name'] ?? ('Location (' . number_format($lat, 5) . ', ' . number_format($lng, 5) . ')');

            return response()->json([
                'success' => true,
                'address' => $address,
                'latitude' => $lat,
                'longitude' => $lng,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => true,
                'address' => 'Pinned Location (' . number_format($lat, 5) . ', ' . number_format($lng, 5) . ')',
                'latitude' => $lat,
                'longitude' => $lng,
            ]);
        }
    }

    /**
     * Radius Search Studio - Haversine Filter
     */
    public function radiusSearch(Request $request)
    {
        $centerLat = $request->filled('center_lat') ? (float) $request->center_lat : 23.0225;
        $centerLng = $request->filled('center_lng') ? (float) $request->center_lng : 72.5714;
        $radiusKm = $request->filled('radius') ? (float) $request->radius : 10; // default 10 km
        $centerAddress = $request->get('center_address', 'Selected Center Point');

        if ($request->filled('location_id')) {
            $centerLoc = Location::find($request->location_id);
            if ($centerLoc) {
                $centerLat = $centerLoc->latitude;
                $centerLng = $centerLoc->longitude;
                $centerAddress = $centerLoc->address;
            }
        }

        $allLocations = Location::all();
        $nearbyLocations = [];

        foreach ($allLocations as $location) {
            $dist = $this->calculateDistanceInKm(
                $centerLat,
                $centerLng,
                $location->latitude,
                $location->longitude
            );

            if ($dist <= $radiusKm) {
                $location->distance_km = round($dist, 2);
                $location->distance_miles = round($dist * 0.621371, 2);
                $nearbyLocations[] = $location;
            }
        }

        usort($nearbyLocations, fn($a, $b) => $a->distance_km <=> $b->distance_km);

        $locationsList = Location::orderBy('id', 'asc')->get();

        return view('locations.radius_search', compact(
            'centerLat',
            'centerLng',
            'radiusKm',
            'centerAddress',
            'nearbyLocations',
            'locationsList'
        ));
    }

    /**
     * Route Planner view
     */
    public function routePlanner(Request $request)
    {
        $locations = Location::orderBy('id', 'asc')->get();
        return view('locations.route_planner', compact('locations'));
    }

    /**
     * Calculate Multi-Stop Route & Waypoints
     */
    public function calculateRoute(Request $request)
    {
        $request->validate([
            'waypoints' => 'required|array|min:2',
            'waypoints.*' => 'required|exists:locations,id',
        ]);

        $selectedIds = $request->waypoints;
        $waypoints = [];

        foreach ($selectedIds as $id) {
            $loc = Location::find($id);
            if ($loc) {
                $waypoints[] = $loc;
            }
        }

        $totalDistanceKm = 0;
        $segments = [];

        for ($i = 0; $i < count($waypoints) - 1; $i++) {
            $from = $waypoints[$i];
            $to = $waypoints[$i + 1];

            $legKm = $this->calculateDistanceInKm(
                $from->latitude,
                $from->longitude,
                $to->latitude,
                $to->longitude
            );

            $totalDistanceKm += $legKm;

            $segments[] = [
                'from' => $from,
                'to' => $to,
                'distance_km' => round($legKm, 2),
                'distance_miles' => round($legKm * 0.621371, 2),
            ];
        }

        $totalDistanceMiles = round($totalDistanceKm * 0.621371, 2);
        $totalDistanceKm = round($totalDistanceKm, 2);

        $avgSpeedKmH = 50;
        $totalMinutes = round(($totalDistanceKm / $avgSpeedKmH) * 60);
        $hours = floor($totalMinutes / 60);
        $mins = $totalMinutes % 60;
        $estimatedTimeFormatted = ($hours > 0 ? "{$hours}h " : "") . "{$mins} mins";

        $originStr = rawurlencode("{$waypoints[0]->latitude},{$waypoints[0]->longitude}");
        $destStr = rawurlencode("{$waypoints[count($waypoints) - 1]->latitude},{$waypoints[count($waypoints) - 1]->longitude}");

        $intermediate = [];
        for ($i = 1; $i < count($waypoints) - 1; $i++) {
            $intermediate[] = rawurlencode("{$waypoints[$i]->latitude},{$waypoints[$i]->longitude}");
        }

        $googleMapsUrl = "https://www.google.com/maps/dir/?api=1&origin={$originStr}&destination={$destStr}";
        if (!empty($intermediate)) {
            $googleMapsUrl .= "&waypoints=" . implode('|', $intermediate);
        }

        $locations = Location::orderBy('id', 'asc')->get();

        return view('locations.route_planner', compact(
            'locations',
            'waypoints',
            'segments',
            'totalDistanceKm',
            'totalDistanceMiles',
            'estimatedTimeFormatted',
            'googleMapsUrl'
        ));
    }

    /**
     * Export Route as GPX file
     */
    public function exportGpx(Request $request)
    {
        $request->validate([
            'waypoint_ids' => 'required|string',
        ]);

        $ids = array_filter(explode(',', $request->waypoint_ids));
        $locations = Location::whereIn('id', $ids)->get();

        $gpx = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $gpx .= '<gpx version="1.1" creator="Laravel 12 Geocoder Studio" xmlns="http://www.topografix.com/GPX/1/1">' . "\n";
        $gpx .= "  <trk>\n    <name>Multi-Stop Optimized Route</name>\n    <trkseg>\n";

        foreach ($locations as $loc) {
            $gpx .= sprintf("      <trkpt lat=\"%f\" lon=\"%f\"><name>%s</name></trkpt>\n", $loc->latitude, $loc->longitude, htmlspecialchars($loc->address));
        }

        $gpx .= "    </trkseg>\n  </trk>\n</gpx>";

        return response($gpx, 200, [
            'Content-Type' => 'application/gpx+xml',
            'Content-Disposition' => 'attachment; filename="route-' . date('Y-m-d-His') . '.gpx"',
        ]);
    }

    /**
     * Export Route as KML file
     */
    public function exportKml(Request $request)
    {
        $request->validate([
            'waypoint_ids' => 'required|string',
        ]);

        $ids = array_filter(explode(',', $request->waypoint_ids));
        $locations = Location::whereIn('id', $ids)->get();

        $kml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $kml .= '<kml xmlns="http://www.opengis.net/kml/2.2">' . "\n";
        $kml .= "  <Document>\n    <name>Laravel 12 Geocoder Waypoint Route</name>\n";

        $coordsStr = [];
        foreach ($locations as $loc) {
            $coordsStr[] = "{$loc->longitude},{$loc->latitude},0";
            $kml .= "    <Placemark>\n";
            $kml .= "      <name>" . htmlspecialchars($loc->address) . "</name>\n";
            $kml .= sprintf("      <Point><coordinates>%f,%f,0</coordinates></Point>\n", $loc->longitude, $loc->latitude);
            $kml .= "    </Placemark>\n";
        }

        $kml .= "    <Placemark>\n      <name>Route Line</name>\n      <LineString>\n        <coordinates>\n          " . implode("\n          ", $coordsStr) . "\n        </coordinates>\n      </LineString>\n    </Placemark>\n";
        $kml .= "  </Document>\n</kml>";

        return response($kml, 200, [
            'Content-Type' => 'application/vnd.google-earth.kml+xml',
            'Content-Disposition' => 'attachment; filename="route-' . date('Y-m-d-His') . '.kml"',
        ]);
    }
}