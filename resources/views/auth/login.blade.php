<x-auth-layout title="تسجيل دخول" heading="تسجيل دخول"
    subtitle="يمكنك تسجيل الدخول من خلال إدخال البريد الإلكتروني وكلمة المرور">

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="field">
            <svg class="icon" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
            <input class="input" type="email" name="email" value="{{ old('email') }}"
                placeholder="البريد الألكتروني" required autofocus>
        </div>

        <div class="field" x-data>
            <svg class="icon" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><circle cx="8" cy="15" r="4"/><path d="m10.85 12.15 8.15-8.15m-3 0 3 0 0 3m-6 3 2 2"/></svg>
            <input class="input" type="password" name="password" placeholder="كلمة المرور" required
                id="pw">
            <button type="button" class="eye" onclick="togglePw('pw', this)" aria-label="إظهار">
                <svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
        </div>

        <div class="between">
            <span></span>
            <a href="{{ route('password.request') }}" class="link">هل نسيت كلمة المرور ؟</a>
        </div>

        <button type="submit" class="btn">تسجيل الدخول</button>
    </form>

    @include('auth.partials.social')

    <p class="center-link">
        ليس لديك حساب؟ <a href="{{ route('register') }}" class="link">تسجيل حساب جديد</a>
    </p>
</x-auth-layout>
