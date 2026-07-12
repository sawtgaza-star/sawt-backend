<?php

use App\Http\Controllers\Api\PayPalController;
use App\Http\Controllers\Api\ReelController;
use Illuminate\Support\Facades\Route;

// Public: Instagram reels from the platform's own account
Route::get('/reels', [ReelController::class, 'index'])->name('api.reels');

// ===== PayPal payments (donations + courses) =====
Route::prefix('paypal')->group(function () {
    Route::get('/config', [PayPalController::class, 'config']);

    // Donations — guest allowed
    Route::post('/donations/order', [PayPalController::class, 'createDonationOrder']);

    // Courses — must be logged in
    Route::post('/courses/{course}/order', [PayPalController::class, 'createCourseOrder'])
        ->middleware('auth:sanctum');

    // Capture an approved order (from Smart Button onApprove)
    Route::post('/orders/{orderId}/capture', [PayPalController::class, 'capture']);

    // Server-to-server webhook (verified inside the controller)
    Route::post('/webhook', [PayPalController::class, 'webhook']);
});
