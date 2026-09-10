<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\CustomerPickupRequestController;
use App\Http\Controllers\Api\VendorCollectionJobController;
use App\Http\Controllers\Api\VendorSiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vendor Mobile App API
|--------------------------------------------------------------------------
*/

// Public: login (no token required). Throttling of failed attempts is handled
// inside AuthController::login so a correct password is never rate-limited.
Route::post('/vendor/login', [AuthController::class, 'login']);

// Public: customer auth
Route::post('/customer/register', [CustomerAuthController::class, 'register'])
    ->middleware('throttle:vendor-login');
Route::post('/customer/login', [CustomerAuthController::class, 'login'])
    ->middleware('throttle:vendor-login');

// Protected: all routes below require a valid Sanctum token with 'vendor' ability
Route::middleware(['auth:sanctum', 'ability:vendor'])->prefix('vendor')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/sites', [VendorSiteController::class, 'index']);

    // Collection Jobs
    Route::get('/jobs', [VendorCollectionJobController::class, 'index']);
    Route::get('/jobs/{collectionJob}', [VendorCollectionJobController::class, 'show']);
    Route::post('/jobs/{collectionJob}/complete', [VendorCollectionJobController::class, 'complete']);
});

// Protected: customer mobile app routes
Route::middleware(['auth:sanctum', 'ability:customer'])->prefix('customer')->group(function () {
    Route::post('/logout', [CustomerAuthController::class, 'logout']);
    Route::get('/me', [CustomerAuthController::class, 'me']);

    Route::get('/pickup-requests', [CustomerPickupRequestController::class, 'index']);
    Route::post('/pickup-requests', [CustomerPickupRequestController::class, 'store']);
    Route::get('/pickup-requests/{pickupRequest}', [CustomerPickupRequestController::class, 'show']);
});
