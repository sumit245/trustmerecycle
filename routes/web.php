<?php

use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect(auth()->user()->isAdmin() ? '/admin' : '/vendor/dashboard');
    }
    return redirect('/admin/login');
});

// Authentication routes (will be added by Breeze)
require __DIR__.'/auth.php';

// Vendor routes
Route::middleware(['auth', 'vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', [VendorController::class, 'dashboard'])->name('dashboard');
    Route::post('/scrap/add', [VendorController::class, 'storeEntry'])->name('scrap.add');
    Route::post('/job/{job}/complete', [VendorController::class, 'completeJob'])->name('job.complete');
});
