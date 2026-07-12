<x-auth-layout title="نسيت كلمة المرور" heading="نسيت كلمة المرور؟"
    subtitle="أدخل بريدك الإلكتروني المسجل وسنرسل لك رمزاً آمناً لإعادة تعيين كلمة المرور الخاصة بك">

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="field">
            <svg class="icon" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
            <input class="input" type="email" name="email" value="{{ old('email') }}" placeholder="البريد الألكتروني" required autofocus>
        </div>

        <button type="submit" class="btn">إرسال</button>
    </form>

    <p class="center-link">
        <a href="{{ route('login') }}" class="link">العودة لتسجيل الدخول</a>
    </p>
</x-auth-layout>
