<?php

use App\Http\Controllers\VendorController;
use App\Http\Controllers\VendorManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect(auth()->user()->isAdmin() ? '/admin' : '/vendor');
    }
    return redirect('/admin/login');
});

// Authentication routes (will be added by Breeze)
require __DIR__.'/auth.php';

// Vendor management routes (admin only)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('vendors', VendorManagementController::class)->except(['show', 'edit', 'update']);
    Route::post('vendors/import', [VendorManagementController::class, 'import'])->name('vendors.import');
});

// Vendor routes (legacy - redirecting to Filament panel)
// The old dashboard route redirects to the new Filament vendor panel
Route::middleware(['auth', 'vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', function () {
        return redirect('/vendor');
    })->name('dashboard');
    // Keep job completion route for now (can be migrated to Filament action later if needed)
    Route::post('/job/{job}/complete', [VendorController::class, 'completeJob'])->name('job.complete');
});
