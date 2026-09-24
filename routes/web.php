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

// Store and geocode address
Route::post('/location', [
    LocationController::class,
    'store'
])->name('location.store');

// Analytics dashboard
Route::get('/location/dashboard', [
    LocationController::class,
    'dashboard'
])->name('location.dashboard');

// CSV export
Route::get('/location/export', [
    LocationController::class,
    'export'
])->name('location.export');

// Distance calculator page
Route::get('/location/distance', [
    LocationController::class,
    'distance'
])->name('location.distance');

// Calculate distance
Route::post('/location/distance', [
    LocationController::class,
    'calculateDistance'
])->name('location.distance.calculate');

// Individual location details
Route::get('/location/{location}', [
    LocationController::class,
    'show'
])->name('location.show');