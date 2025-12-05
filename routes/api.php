<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MonitoringController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $user->photo_url = $user->photo
            ? url('storage/profiles/' . $user->photo)
            : null; // Or default image
        return $user;
    });

    Route::get('/schedule/active', [MonitoringController::class, 'getActiveSchedule']);
    Route::post('/location/update', [MonitoringController::class, 'updateLocation']);
    Route::get('/attendance/history', [MonitoringController::class, 'getAttendanceHistory']);
    Route::post('/scan', [App\Http\Controllers\AttendanceController::class, 'store']);

    // Existing public monitoring endpoint (if needed to be protected or separate)
    // Route::get('/monitoring', ...); 
});

// Public monitoring endpoints (used by the web frontend via AJAX)
Route::get('/monitoring', [App\Http\Controllers\MonitoringController::class, 'getMonitoringData']);
Route::get('/monitoring/block', [App\Http\Controllers\MonitoringController::class, 'getBlockData']);
