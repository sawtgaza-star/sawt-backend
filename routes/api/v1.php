<?php

use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\CreatorsPageController;
use App\Http\Controllers\Api\AboutController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContentPageController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\LayoutController;
use App\Http\Controllers\Api\PayPalController;
use App\Http\Controllers\Api\ReelController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\SupportRequestController;
use App\Http\Controllers\Api\SupportSubscriptionController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

// Public: Instagram reels from the platform's own account
Route::get('/reels', [ReelController::class, 'index'])->name('reels');

// ===== Layout (navbar / footer) =====
Route::prefix('layout')->name('layout.')->group(function () {
    Route::get('/', [LayoutController::class, 'show'])->name('index');
    Route::get('/navbar', [LayoutController::class, 'navbar'])->name('navbar');
    Route::get('/footer', [LayoutController::class, 'footer'])->name('footer');
});

// ===== Pages (home, about, team, creators, …) =====
Route::prefix('pages')->name('pages.')->group(function () {
    Route::get('/home', [HomeController::class, 'show'])->name('home');

    Route::get('/content', [ContentPageController::class, 'show'])->name('content');

    Route::get('/blogs', [BlogController::class, 'index'])->name('blogs');
    Route::get('/blogs/{uuid}', [BlogController::class, 'show'])->name('blogs.show');

    Route::get('/about', [AboutController::class, 'show'])->name('about');

    Route::get('/team', [TeamController::class, 'index'])->name('team');
    Route::get('/team/{uuid}', [TeamController::class, 'show'])->name('team.show');

    Route::get('/creators', [CreatorsPageController::class, 'index'])->name('creators');
    Route::get('/creators/all', [CreatorsPageController::class, 'all'])->name('creators.all');
    Route::post('/creators/join', [CreatorsPageController::class, 'join'])->name('creators.join');
    Route::get('/creators/{uuid}', [CreatorsPageController::class, 'show'])->name('creators.show');
});

// ===== Auth (JWT) =====
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password');
    Route::post('/resend-code', [AuthController::class, 'resendResetCode'])->name('auth.resend-code');
    Route::post('/verify-code', [AuthController::class, 'verifyResetCode'])->name('auth.verify-code');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset-password');

    Route::middleware('auth:api')->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    });
});

// ===== ادعم صوت — المحتوى العام (بدون هيدر/فوتر) =====
Route::prefix('support')->name('support.')->group(function () {
    Route::get('/methods', [SupportController::class, 'methods'])->name('methods');
    Route::get('/methods/category/{category}', [SupportController::class, 'category'])->name('methods.category');
    Route::get('/methods/{uuid}', [SupportController::class, 'show'])->name('methods.show');
    Route::get('/plans', [SupportController::class, 'plans'])->name('plans');
    Route::get('/wizard', [SupportController::class, 'wizard'])->name('wizard');
    Route::get('/team-options', [SupportController::class, 'teamOptions'])->name('team-options');

    // ===== ويزارد الدعم — أربع خطوات، الضيف مسموح =====
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::post('/', [SupportRequestController::class, 'store'])->name('store');                       // 1) الوسيلة
        Route::get('/{uuid}', [SupportRequestController::class, 'show'])->name('show');
        Route::post('/{uuid}/proof', [SupportRequestController::class, 'storeProof'])->name('proof');       // 2) الإثبات
        Route::post('/{uuid}/team', [SupportRequestController::class, 'storeTeamStep'])->name('team');      // 3) دعم الفريق
        Route::post('/{uuid}/contact', [SupportRequestController::class, 'storeContactStep'])->name('contact'); // 4) التواصل
        Route::post('/{uuid}/paypal/order', [SupportRequestController::class, 'createPayPalOrder'])->name('paypal.order');
    });

    // ===== الاشتراكات الدورية (PayPal Billing) =====
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::post('/', [SupportSubscriptionController::class, 'store'])->name('store');
        Route::get('/{uuid}', [SupportSubscriptionController::class, 'show'])->name('show');
        Route::post('/{uuid}/activate', [SupportSubscriptionController::class, 'activate'])->name('activate');
        Route::post('/{uuid}/cancel', [SupportSubscriptionController::class, 'cancel'])->name('cancel');
    });

    Route::middleware('auth:api')->get('/my-subscriptions', [SupportSubscriptionController::class, 'mine'])->name('my-subscriptions');
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
