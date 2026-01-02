<?php

use App\Http\Controllers\VendorController;
use App\Http\Controllers\VendorManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect(auth()->user()->isAdmin() ? '/admin' : '/vendor/dashboard');
    }
    return view('welcome');
});

// Authentication routes (will be added by Breeze)
require __DIR__.'/auth.php';

// Vendor management routes (admin only)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('vendors', VendorManagementController::class)->except(['show', 'edit', 'update']);
    Route::post('vendors/import', [VendorManagementController::class, 'import'])->name('vendors.import');
});

// Vendor routes
Route::middleware(['auth', 'vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', [VendorController::class, 'dashboard'])->name('dashboard');
    Route::post('/scrap/add', [VendorController::class, 'storeEntry'])->name('scrap.add');
    Route::post('/job/{job}/complete', [VendorController::class, 'completeJob'])->name('job.complete');
});
