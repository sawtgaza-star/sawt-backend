<x-auth-layout title="التحقق من الرمز" heading="التحقق من الرمز"
    subtitle="تم ارسال رمز مكون من 6 ارقام الى البريد الإلكتروني">

    <form method="POST" action="{{ route('password.otp.verify') }}" id="otpForm">
        @csrf
        <input type="hidden" name="code" id="otpCode">

        <div class="otp" dir="ltr">
            @for ($i = 0; $i < 6; $i++)
                <input type="text" inputmode="numeric" maxlength="1" class="otp-box" data-index="{{ $i }}" autocomplete="off">
            @endfor
        </div>

        <p style="text-align:center; font-weight:600; color:var(--muted); margin-bottom:1.4rem;">
            لم تستلم رمزاً؟
            <a href="{{ route('password.otp.resend') }}" class="link"
               id="resendLink" style="pointer-events:none; opacity:.5;">إعادة الإرسال</a>
        </p>

        <button type="submit" class="btn">التحقق</button>

        <div class="timer" id="timer">00 : 15</div>
    </form>

    @push('scripts')
        <script>
            // إدخال OTP: انتقال تلقائي بين الخانات + لصق
            const boxes = [...document.querySelectorAll('.otp-box')];
            const codeField = document.getElementById('otpCode');

            function sync() { codeField.value = boxes.map(b => b.value).join(''); }

            boxes.forEach((box, i) => {
                box.addEventListener('input', () => {
                    box.value = box.value.replace(/\D/g, '').slice(0, 1);
                    if (box.value && i < boxes.length - 1) boxes[i + 1].focus();
                    sync();
                });
                box.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !box.value && i > 0) boxes[i - 1].focus();
                });
                box.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const digits = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6).split('');
                    digits.forEach((d, k) => { if (boxes[k]) boxes[k].value = d; });
                    sync();
                    (boxes[digits.length] || boxes[5]).focus();
                });
            });
            boxes[0]?.focus();

            // مؤقّت إعادة الإرسال 15 ثانية
            let t = 15;
            const timerEl = document.getElementById('timer');
            const resend = document.getElementById('resendLink');
            const tick = setInterval(() => {
                t--;
                timerEl.textContent = '00 : ' + String(Math.max(t, 0)).padStart(2, '0');
                if (t <= 0) {
                    clearInterval(tick);
                    timerEl.textContent = '';
                    resend.style.pointerEvents = 'auto';
                    resend.style.opacity = '1';
                }
            }, 1000);
        </script>
    @endpush
</x-auth-layout>
