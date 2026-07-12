<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    private const OTP_TTL_MINUTES = 15;

    /* ---------- 1) نسيت كلمة المرور ---------- */

    public function showForgot()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['email' => ['required', 'email', 'exists:users,email']]);

        $this->issueOtp($request->email);
        $request->session()->put('otp_email', $request->email);

        return redirect()->route('password.otp')->with('status', 'تم إرسال رمز التحقق إلى بريدك.');
    }

    /* ---------- 2) التحقق من الرمز ---------- */

    public function showOtp(Request $request)
    {
        if (! $request->session()->get('otp_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        $email = $request->session()->get('otp_email');

        if (! $email || ! $this->checkOtp($email, $request->code)) {
            throw ValidationException::withMessages(['code' => 'الرمز غير صحيح أو منتهي الصلاحية.']);
        }

        $request->session()->put('otp_verified', $email);

        return redirect()->route('password.reset');
    }

    public function resendOtp(Request $request)
    {
        $email = $request->session()->get('otp_email');

        if ($email) {
            $this->issueOtp($email);
        }

        return redirect()->route('password.otp')->with('status', 'تم إعادة إرسال الرمز.');
    }

    /* ---------- 3) كلمة مرور جديدة ---------- */

    public function showReset(Request $request)
    {
        if (! $request->session()->get('otp_verified')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $email = $request->session()->get('otp_verified');

        if (! $email) {
            return redirect()->route('password.request');
        }

        User::where('email', $email)->update(['password' => Hash::make($request->password)]);
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        $request->session()->forget(['otp_email', 'otp_verified']);

        return redirect()->route('login')->with('status', 'تم تغيير كلمة المرور بنجاح. يمكنك تسجيل الدخول الآن.');
    }

    /* ---------- helpers ---------- */

    private function issueOtp(string $email): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => Hash::make($code), 'created_at' => now()],
        );

        // MAIL_MAILER=log → الرمز يُكتب في storage/logs. بدّلها ببريد فعلي في الإنتاج.
        Mail::raw("رمز إعادة تعيين كلمة المرور الخاص بك هو: {$code}", function ($m) use ($email) {
            $m->to($email)->subject('رمز التحقق - صوت');
        });
    }

    private function checkOtp(string $email, string $code): bool
    {
        $row = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $row) {
            return false;
        }

        $notExpired = Carbon::parse($row->created_at)->addMinutes(self::OTP_TTL_MINUTES)->isFuture();

        return $notExpired && Hash::check($code, $row->token);
    }
}
