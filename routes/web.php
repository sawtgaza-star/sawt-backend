<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

/* ===== Pages Routes ===== */
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/content', [PageController::class, 'content'])->name('content');

/* ===== الكورسات الحضورية ===== */
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
Route::post('/courses/{course}/join', [CourseController::class, 'join'])
    ->middleware('auth')
    ->name('courses.join');

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

// خريطة الموقع + robots — لتحسين الأرشفة في محركات البحث
Route::get('/sitemap.xml', function () {
    $urls = [
        ['loc' => url('/'), 'freq' => 'daily', 'priority' => '1.0'],
        ['loc' => route('donate'), 'freq' => 'weekly', 'priority' => '0.8'],
        ['loc' => route('login'), 'freq' => 'monthly', 'priority' => '0.5'],
        ['loc' => route('register'), 'freq' => 'monthly', 'priority' => '0.5'],
    ];

    return response()->view('sitemap', ['urls' => $urls])
        ->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin',
        'Disallow: /login',
        'Disallow: /register',
        '',
        'Sitemap: '.url('/sitemap.xml'),
    ];

    return response(implode("\n", $lines), 200)
        ->header('Content-Type', 'text/plain');
});

// تنزيل إثبات التحويل — للوحة التحكم فقط (الملفات على قرص خاص)
Route::get('/admin/support/proofs/{uuid}', [\App\Http\Controllers\SupportProofController::class, 'download'])
    ->middleware('auth')
    ->name('support.proofs.download');

Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['ar', 'en'], true)) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }

    return redirect()->back();
})->name('lang.switch');
