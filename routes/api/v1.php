<?php

use App\Http\Controllers\Api\AboutController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PayPalController;
use App\Http\Controllers\Api\ReelController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

// Public: Instagram reels from the platform's own account
Route::get('/reels', [ReelController::class, 'index'])->name('reels');

// ===== Pages (about, team, …) — no header/footer =====
Route::prefix('pages')->name('pages.')->group(function () {
    Route::get('/about', [AboutController::class, 'show'])->name('about');

    Route::get('/team', [TeamController::class, 'index'])->name('team');
    Route::get('/team/{uuid}', [TeamController::class, 'show'])->name('team.show');
});

// ===== Auth (JWT) =====
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

    Route::middleware('auth:api')->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    });
});

// ===== PayPal payments (donations) =====
Route::prefix('paypal')->group(function () {
    Route::get('/config', [PayPalController::class, 'config'])->name('paypal.config');

    // Donations — guest allowed
    Route::post('/donations/order', [PayPalController::class, 'createDonationOrder'])->name('paypal.donations.order');

    // Capture an approved order (from Smart Button onApprove)
    Route::post('/orders/{orderId}/capture', [PayPalController::class, 'capture'])->name('paypal.orders.capture');

    // Server-to-server webhook   (verified inside the controller)
    Route::post('/webhook', [PayPalController::class, 'webhook'])->name('paypal.webhook');
});
