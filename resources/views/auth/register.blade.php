<x-auth-layout title="تسجيل حساب جديد" heading="تسجيل حساب جديد"
    subtitle="أنشئ حساب مع صوت و تابع اخر التطورات">

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="row-2">
            <div class="field">
                <svg class="icon" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
                <input class="input" type="text" name="first_name" value="{{ old('first_name') }}" placeholder="الاسم الأول" required autofocus>
            </div>
            <div class="field no-icon">
                <input class="input" type="text" name="last_name" value="{{ old('last_name') }}" placeholder="اسم العائلة" required>
            </div>
        </div>

        <div class="field">
            <svg class="icon" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
            <input class="input" type="email" name="email" value="{{ old('email') }}" placeholder="البريد الإلكتروني" required>
        </div>

        <div class="field">
            <svg class="icon" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><circle cx="8" cy="15" r="4"/><path d="m10.85 12.15 8.15-8.15m-3 0 3 0 0 3m-6 3 2 2"/></svg>
            <input class="input" type="password" name="password" placeholder="كلمة المرور" required id="reg_pw">
            <button type="button" class="eye" onclick="togglePw('reg_pw')" aria-label="إظهار">
                <svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
        </div>

        <label class="check">
            <input type="checkbox" name="remember" value="1"> تذكرني
        </label>

        <button type="submit" class="btn">انشئ حساب</button>
    </form>

    @include('auth.partials.social')

    <p class="center-link">
        هل لديك حساب؟ <a href="{{ route('login') }}" class="link">تسجيل دخول</a>
    </p>
</x-auth-layout>
