<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Services\GeocoderService;

class LocationController extends Controller
{
    protected $geo; // Geocoder service instance

    public function __construct(GeocoderService $geo)
    {
        $this->geo = $geo; // Inject GeocoderService using dependency injection
    }

    // Display location page with saved records
    public function index()
    {
        $locations = Location::latest()->get(); // Fetch all locations (latest first)
        return view('location', compact('locations')); // Load blade view
    }

    // Store address and convert it into coordinates
    public function store(Request $request)
    {
        $request->validate([
            'address' => 'required|string' // Validate address input
        ]);

        $result = $this->geo->geocode($request->address); // Get latitude & longitude

        if (!$result) {
            return back()->with('error','Location not found'); // Handle invalid address
        }

        Location::create([
            'address' => $request->address,
            'latitude' => $result->getCoordinates()->getLatitude(), // Save latitude
            'longitude' => $result->getCoordinates()->getLongitude(), // Save longitude
        ]);

        return back()->with('success','Location Saved!'); // Success message
    }
}