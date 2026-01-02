<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthCheckController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Health Check Routes (must be before the catch-all)
Route::get('/health-check', [HealthCheckController::class, 'page']);
Route::get('/health-check/api', [HealthCheckController::class, 'index']);

// Serve the Vue.js SPA for all routes
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
