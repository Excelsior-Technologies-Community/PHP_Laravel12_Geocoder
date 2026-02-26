<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;

// Display location page (form + saved locations list)
Route::get('/location', [LocationController::class,'index']);

// Handle form submission and save geocoded location
Route::post('/location', [LocationController::class,'store'])->name('location.store');