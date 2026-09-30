<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;

/*
|--------------------------------------------------------------------------
| Geocoder Routes
|--------------------------------------------------------------------------
*/

// Main geocoder page
Route::get('/location', [
    LocationController::class,
    'index'
])->name('location.index');


// Store new location
Route::post('/location', [
    LocationController::class,
    'store'
])->name('location.store');


// Dashboard
Route::get('/location/dashboard', [
    LocationController::class,
    'dashboard'
])->name('location.dashboard');


// CSV export
Route::get('/location/export', [
    LocationController::class,
    'export'
])->name('location.export');


// JSON export
Route::get('/location/export-json', [
    LocationController::class,
    'exportJson'
])->name('location.export.json');


// Distance calculator
Route::get('/location/distance', [
    LocationController::class,
    'distance'
])->name('location.distance');


// Calculate distance
Route::post('/location/distance', [
    LocationController::class,
    'calculateDistance'
])->name('location.distance.calculate');


// Bulk delete
Route::post('/location/bulk-delete', [
    LocationController::class,
    'bulkDelete'
])->name('location.bulk-delete');


// Reverse Geocoding API endpoint
Route::get('/location/reverse-geocode', [
    LocationController::class,
    'reverseGeocode'
])->name('location.reverse-geocode');


// Radius Search Studio
Route::get('/location/radius-search', [
    LocationController::class,
    'radiusSearch'
])->name('location.radius-search');


// Multi-stop Route Planner
Route::get('/location/route-planner', [
    LocationController::class,
    'routePlanner'
])->name('location.route-planner');


// Calculate Multi-stop Route
Route::post('/location/calculate-route', [
    LocationController::class,
    'calculateRoute'
])->name('location.calculate-route');


// Export Route as GPX
Route::post('/location/export-gpx', [
    LocationController::class,
    'exportGpx'
])->name('location.export-gpx');


// Export Route as KML
Route::post('/location/export-kml', [
    LocationController::class,
    'exportKml'
])->name('location.export-kml');


// Edit location
Route::get('/location/{location}/edit', [
    LocationController::class,
    'edit'
])->name('location.edit');


// Update location
Route::put('/location/{location}', [
    LocationController::class,
    'update'
])->name('location.update');


// Delete location
Route::delete('/location/{location}', [
    LocationController::class,
    'destroy'
])->name('location.destroy');


// Individual location
Route::get('/location/{location}', [
    LocationController::class,
    'show'
])->name('location.show');