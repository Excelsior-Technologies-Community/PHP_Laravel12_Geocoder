# 🌍 PHP_Laravel12_Geocoder

![Laravel](https://img.shields.io/badge/Laravel-12-red)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)
![OpenStreetMap](https://img.shields.io/badge/OpenStreetMap-Nominatim-green)

---

##  Overview

**PHP_Laravel12_Geocoder** is a Laravel 12 web application that converts an address into **Latitude & Longitude** using the **OpenStreetMap Nominatim Geocoder** and visualizes saved locations on an interactive **Leaflet Map**.

Users can:

* Enter an address
* Convert it into geographic coordinates
* Store results in the database
* View all locations in a table
* Display markers on a live map

---

##  Features

* Address → Coordinates (Geocoding)
* OpenStreetMap (Free, No API Key Required)
* Laravel Service-Based Architecture
* Blade Web UI
* Database Storage
* Leaflet Interactive Map
* Multiple Marker Display

---

##  Folder Structure

```
app/
 ├── Http/
 │   └── Controllers/
 │       └── LocationController.php
 │
 ├── Models/
 │   └── Location.php
 │
 └── Services/
     └── GeocoderService.php

resources/
 └── views/
     └── location.blade.php

routes/
 └── web.php

database/
 └── migrations/
     └── create_locations_table.php
```

---

## Requirements

* PHP 8.2+
* Composer
* Laravel 12
* MySQL / MariaDB
* XAMPP / Laravel Sail (optional)

---

## Step 1 — Create Laravel Project

```bash
composer create-project laravel/laravel laravel-geocoder

php artisan serve
```

---

## Step 2 — Install Required Packages

```bash
composer require geocoder-php/nominatim-provider

composer require php-http/guzzle7-adapter

composer require guzzlehttp/guzzle
```

---

## Step 3 — Database Setup

Update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

---

## Step 4 — Create Migration

```bash
php artisan make:migration create_locations_table
```

Edit migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
```

Run:

```bash
php artisan migrate
```

---

## Step 5 — Model

Create:

```bash
php artisan make:model Location
```

`app/Models/Location.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'address',
        'latitude',
        'longitude'
    ];
}
```

---

## Step 6 — Geocoder Service

Create file:

`app/Services/GeocoderService.php`

```php
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

        $this->geocoder = new StatefulGeocoder($provider, 'en');
    }

    public function geocode($address)
    {
        return $this->geocoder
            ->geocodeQuery(GeocodeQuery::create($address))
            ->first();
    }
}
```

---

## Step 7 — Controller

Create:

```bash
php artisan make:controller LocationController
```

`app/Http/Controllers/LocationController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Services\GeocoderService;

class LocationController extends Controller
{
    protected $geo;

    public function __construct(GeocoderService $geo)
    {
        $this->geo = $geo;
    }

    public function index()
    {
        $locations = Location::latest()->get();
        return view('location', compact('locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'address' => 'required|string'
        ]);

        $result = $this->geo->geocode($request->address);

        if (!$result) {
            return back()->with('error','Location not found');
        }

        Location::create([
            'address' => $request->address,
            'latitude' => $result->getCoordinates()->getLatitude(),
            'longitude' => $result->getCoordinates()->getLongitude(),
        ]);

        return back()->with('success','Location Saved!');
    }
}
```

---

## Step 8 — Routes

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;

Route::get('/location', [LocationController::class,'index']);
Route::post('/location', [LocationController::class,'store'])->name('location.store');
```

---

## Step 9 — Blade View with Map

Create:

`resources/views/location.blade.php`

```html
<!DOCTYPE html>
<html>
<head>
    <title>Laravel Geocoder</title>

    <!-- Leaflet Map -->
    <link rel="stylesheet"
     href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>

body{
    font-family:Arial;
    background:#f4f6f9;
    padding:40px;
}

.container{
    max-width:900px;
    margin:auto;
    background:white;
    padding:30px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,.1);
}

h2{text-align:center;}

form{
    display:flex;
    gap:10px;
    margin-bottom:20px;
}

input{
    flex:1;
    padding:12px;
    border:1px solid #ccc;
    border-radius:6px;
}

button{
    background:#1677ff;
    color:white;
    border:none;
    padding:12px 20px;
    border-radius:6px;
    cursor:pointer;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

th{
    background:#1677ff;
    color:white;
    padding:12px;
}

td{
    padding:10px;
    text-align:center;
    border-bottom:1px solid #ddd;
}

#map{
    height:400px;
    margin-top:25px;
    border-radius:8px;
}

.success{background:#d4edda;padding:10px;margin-bottom:10px;}
.error{background:#f8d7da;padding:10px;margin-bottom:10px;}

</style>
</head>

<body>

<div class="container">

<h2>Laravel Geocoder</h2>

@if(session('success'))
<div class="success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="error">{{ session('error') }}</div>
@endif

<form method="POST" action="{{ route('location.store') }}">
    @csrf
    <input type="text" name="address"
           placeholder="Enter Address (Example: Ahmedabad)" required>
    <button>Get Coordinates</button>
</form>

<table>
<tr>
    <th>Address</th>
    <th>Latitude</th>
    <th>Longitude</th>
</tr>

@foreach($locations as $loc)
<tr>
    <td>{{ $loc->address }}</td>
    <td>{{ $loc->latitude }}</td>
    <td>{{ $loc->longitude }}</td>
</tr>
@endforeach

</table>

<h3>Location Map</h3>
<div id="map"></div>

</div>

<script>

const locations = @json($locations);

let map = L.map('map').setView([23.0225,72.5714],6);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    attribution:'© OpenStreetMap'
}).addTo(map);

locations.forEach(loc => {

    if(loc.latitude && loc.longitude){
        L.marker([loc.latitude, loc.longitude])
            .addTo(map)
            .bindPopup(
                `<b>${loc.address}</b><br>
                 Lat: ${loc.latitude}<br>
                 Lng: ${loc.longitude}`
            );
    }

});

</script>

</body>
</html>
```

---

## Step 10 — Run Application

```bash
php artisan serve
```

Open:

```
http://127.0.0.1:8000/location
```
<img width="958" height="240" alt="Screenshot 2026-02-26 155526" src="https://github.com/user-attachments/assets/d4707ee2-c5f8-4c4b-b2d1-923a8567f89c" />

---

## Usage

1. Enter an address (e.g., Ahmedabad)
2. Click Get Coordinates

      <img width="959" height="242" alt="Screenshot 2026-02-26 155618" src="https://github.com/user-attachments/assets/2c44f137-d514-4e90-b91f-c82b94faad82" />
---      
3. Data is saved

   <img width="965" height="448" alt="Screenshot 2026-02-26 161506" src="https://github.com/user-attachments/assets/1b0e47df-556a-4704-9708-9631df6bc86e" />
---
4. Marker appears on map

   <img width="964" height="494" alt="Screenshot 2026-02-26 161422" src="https://github.com/user-attachments/assets/cff1db2a-f2c8-47c8-9dee-6fb313b8c66a" />
---
5. Laravel Geocoder Output (Full)

    <img width="867" height="840" alt="Screenshot 2026-02-26 160951" src="https://github.com/user-attachments/assets/7f16173f-9f51-4535-b51b-dc9d7d6c2f6d" />

---




