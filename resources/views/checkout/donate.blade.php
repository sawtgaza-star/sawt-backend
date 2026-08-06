<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo', ['seoTitle' => 'تبرّع — '.\App\Models\Setting::get('site_name', 'منصة صوت')])
    <style>
        :root { --violet: #7c3aed; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Cairo', system-ui, sans-serif; background: #f5f3ff; color: #1f2937; display: flex; min-height: 100vh; align-items: center; justify-content: center; }
        .card { background: #fff; border-radius: 1.25rem; box-shadow: 0 20px 60px -20px rgba(76,29,149,.35); padding: 2rem; width: 100%; max-width: 420px; }
        h1 { font-size: 1.4rem; margin: 0 0 .25rem; }
        p.sub { color: #6b7280; margin: 0 0 1.5rem; font-size: .9rem; }
        label { display: block; font-size: .85rem; font-weight: 600; margin: .75rem 0 .35rem; }
        input { width: 100%; padding: .7rem .9rem; border: 1px solid #e5e7eb; border-radius: .7rem; font-size: 1rem; font-family: inherit; }
        input:focus { outline: none; border-color: var(--violet); box-shadow: 0 0 0 3px rgba(124,58,237,.15); }
        .amounts { display: flex; gap: .5rem; margin: .5rem 0; }
        .amounts button { flex: 1; padding: .6rem; border: 1px solid #e5e7eb; background: #faf5ff; border-radius: .6rem; cursor: pointer; font-family: inherit; font-weight: 600; }
        .amounts button.active { background: var(--violet); color: #fff; border-color: var(--violet); }
        #paypal-buttons { margin-top: 1.25rem; min-height: 50px; }
        .msg { margin-top: 1rem; padding: .8rem; border-radius: .6rem; font-size: .9rem; display: none; }
        .msg.ok { background: #dcfce7; color: #166534; display: block; }
        .msg.err { background: #fee2e2; color: #991b1b; display: block; }
    </style>
</head>
<body>
    <div class="card">
        <h1>ادعم منصة صوت 💜</h1>
        <p class="sub">تبرّعك يصنع فرقاً. اختر المبلغ وادفع بأمان عبر PayPal.</p>

        @isset($campaign)
            <input type="hidden" id="campaign_id" value="{{ $campaign->id }}">
            <p class="sub">الحملة: <strong>{{ $campaign->title }}</strong></p>
        @endisset

        <label>المبلغ (USD)</label>
        <div class="amounts">
            <button type="button" data-amt="10">$10</button>
            <button type="button" data-amt="25">$25</button>
            <button type="button" data-amt="50">$50</button>
        </div>
        <input type="number" id="amount" min="1" step="1" value="25" placeholder="مبلغ آخر">

        <label>الاسم (اختياري)</label>
        <input type="text" id="donor_name" placeholder="اسمك">

        <label>البريد الإلكتروني (اختياري)</label>
        <input type="email" id="donor_email" placeholder="you@example.com">

        <div id="paypal-buttons"></div>
        <div id="msg" class="msg"></div>
    </div>

    <script>
        const msg = document.getElementById('msg');
        const amountInput = document.getElementById('amount');

        // أزرار المبالغ السريعة
        document.querySelectorAll('.amounts button').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.amounts button').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                amountInput.value = btn.dataset.amt;
            });
        });

        function show(text, ok) {
            msg.textContent = text;
            msg.className = 'msg ' + (ok ? 'ok' : 'err');
        }

        // 1) نجيب إعدادات PayPal (client id + العملة) ثم نحمّل الـ SDK
        fetch('/api/v1/paypal/config')
            .then(r => r.json())
            .then(cfg => {
                if (!cfg.configured) {
                    show('بوابة الدفع غير مهيأة بعد. أضف بيانات PayPal من لوحة الإعدادات.', false);
                    return;
                }
                const s = document.createElement('script');
                s.src = `https://www.paypal.com/sdk/js?client-id=${cfg.client_id}&currency=${cfg.currency}`;
                s.onload = renderButtons;
                document.head.appendChild(s);
            });

        // 2) نرسم أزرار PayPal الذكية
        function renderButtons() {
            paypal.Buttons({
                // إنشاء الطلب على الخادم
                createOrder: () => {
                    const campaignEl = document.getElementById('campaign_id');
                    return fetch('/api/v1/paypal/donations/order', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({
                            amount: parseFloat(amountInput.value || '0'),
                            campaign_id: campaignEl ? campaignEl.value : null,
                            donor_name: document.getElementById('donor_name').value || null,
                            donor_email: document.getElementById('donor_email').value || null,
                        }),
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.error) throw new Error(d.error);
                        return d.id;
                    });
                },
                // بعد موافقة المستخدم: نلتقط الدفع على الخادم
                onApprove: (data) => {
                    return fetch(`/api/v1/paypal/orders/${data.orderID}/capture`, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' },
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.status === 'completed') {
                            show('تم استلام تبرّعك بنجاح. شكراً لدعمك! 💜', true);
                        } else {
                            show('لم تكتمل عملية الدفع. حاول مرة أخرى.', false);
                        }
                    });
                },
                onError: () => show('حدث خطأ أثناء الدفع. حاول مرة أخرى.', false),
            }).render('#paypal-buttons');
        }
    </script>
</body>
</html>
