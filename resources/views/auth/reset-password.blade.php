<x-auth-layout title="كلمة مرور جديدة" heading="تعيين كلمة مرور جديدة"
    subtitle="أعد تعيين كلمة مرورك. يرجى تعيين كلمة مرور جديدة لحسابك">

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <div class="field">
            <svg class="icon" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><circle cx="8" cy="15" r="4"/><path d="m10.85 12.15 8.15-8.15m-3 0 3 0 0 3m-6 3 2 2"/></svg>
            <input class="input" type="password" name="password" placeholder="كلمة المرور الجديدة" required autofocus id="np1">
            <button type="button" class="eye" onclick="togglePw('np1')" aria-label="إظهار">
                <svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
        </div>

        <div class="field">
            <svg class="icon" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><circle cx="8" cy="15" r="4"/><path d="m10.85 12.15 8.15-8.15m-3 0 3 0 0 3m-6 3 2 2"/></svg>
            <input class="input" type="password" name="password_confirmation" placeholder="إعادة ادخال كلمة المرور الجديدة" required id="np2">
            <button type="button" class="eye" onclick="togglePw('np2')" aria-label="إظهار">
                <svg fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
        </div>

        <button type="submit" class="btn">تغيير كلمة المرور</button>
    </form>
</x-auth-layout>
