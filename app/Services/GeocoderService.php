<?php

namespace App\Services;

use Geocoder\Query\GeocodeQuery;
use Geocoder\Provider\Nominatim\Nominatim;
use Geocoder\StatefulGeocoder;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;

class GeocoderService
{
    protected $geocoder;

    public function __construct()
    {
        $httpClient = new GuzzleAdapter();

        $provider = Nominatim::withOpenStreetMapServer(
            $httpClient,
            'Laravel 12 Geocoder'
        );

        $this->geocoder = new StatefulGeocoder(
            $provider,
            'en'
        );
    }

    public function geocode($address)
    {
        return $this->geocoder
            ->geocodeQuery(
                GeocodeQuery::create($address)
            )
            ->first();
    }
}