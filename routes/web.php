<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeolocationController; // Importa el controlador específico

// Define la ruta para la geolocalización
Route::get('/geolocation', [GeolocationController::class, 'getGeolocation']);

// Define una ruta para la vista 'inicio' si es necesario
Route::get('/inicio', function () {
    return view('inicio');
});
