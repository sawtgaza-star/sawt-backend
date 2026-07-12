<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function (\App\Services\InstagramService $instagram) {
    return view('welcome', ['reels' => $instagram->reels(12)]);
});

/* ===== نظام المصادقة (واجهة المستخدمين) ===== */
Route::middleware('guest')->group(function () {
    // تسجيل الدخول
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    // تسجيل حساب جديد
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    // نسيت كلمة المرور (تدفق OTP)
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendOtp'])->name('password.email');

    Route::get('/verify-otp', [PasswordResetController::class, 'showOtp'])->name('password.otp');
    Route::post('/verify-otp', [PasswordResetController::class, 'verifyOtp'])->name('password.otp.verify');
    Route::get('/resend-otp', [PasswordResetController::class, 'resendOtp'])->name('password.otp.resend');

    Route::get('/reset-password', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');

    // تسجيل الدخول الاجتماعي (Placeholder)
    Route::get('/auth/{provider}', [SocialiteController::class, 'redirect'])->name('social.redirect');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')->name('logout');

// صفحة التبرّع (PayPal Smart Buttons) — تعمل للضيوف
Route::get('/donate/{campaign?}', function (?App\Models\Campaign $campaign = null) {
    return view('checkout.donate', ['campaign' => $campaign]);
})->name('donate');

Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['ar', 'en'], true)) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }

    return redirect()->back();
})->name('lang.switch');
