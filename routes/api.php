<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VendorCollectionJobController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vendor Mobile App API
|--------------------------------------------------------------------------
*/

// Public: login (no token required)
Route::post('/vendor/login', [AuthController::class, 'login'])
    ->middleware('throttle:vendor-login');

// Protected: all routes below require a valid Sanctum token with 'vendor' ability
Route::middleware(['auth:sanctum', 'ability:vendor'])->prefix('vendor')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Collection Jobs
    Route::get('/jobs', [VendorCollectionJobController::class, 'index']);
    Route::get('/jobs/{collectionJob}', [VendorCollectionJobController::class, 'show']);
    Route::post('/jobs/{collectionJob}/complete', [VendorCollectionJobController::class, 'complete']);
});
