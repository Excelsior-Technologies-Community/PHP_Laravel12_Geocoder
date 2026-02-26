<?php

namespace App\Services;

use Geocoder\Query\GeocodeQuery;
use Geocoder\Provider\Nominatim\Nominatim;
use Geocoder\StatefulGeocoder;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;

class GeocoderService
{
    protected $geocoder; // Stores geocoder instance

    public function __construct()
    {
        $httpClient = new GuzzleAdapter(); // Create HTTP client adapter

        $provider = Nominatim::withOpenStreetMapServer(
            $httpClient,
            'Laravel 12 Geocoder' // Set OpenStreetMap provider with user agent
        );

        $this->geocoder = new StatefulGeocoder($provider, 'en'); // Initialize geocoder with language
    }

    public function geocode($address)
    {
        return $this->geocoder
            ->geocodeQuery(GeocodeQuery::create($address)) // Convert address to coordinates
            ->first(); // Return first result
    }
}