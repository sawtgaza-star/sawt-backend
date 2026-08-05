@props(['title' => '', 'heading' => '', 'subtitle' => ''])

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.seo', ['seoTitle' => trim($title).' — '.\App\Models\Setting::get('site_name', 'منصة صوت')])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --green: #4b5a2b;
            --green-dark: #3f4a24;
            --orange: #e07a3e;
            --ink: #1f2937;
            --muted: #6b7280;
            --border: #e5e7eb;
            --field: #ffffff;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: 'Cairo', system-ui, sans-serif;
            color: var(--ink);
            background: #fff;
            min-height: 100vh;
        }
        .auth-wrap { display: flex; min-height: 100vh; }

        /* right brand panel (first child = right in RTL) */
        .brand {
            flex: 1 1 48%;
            background: var(--green-dark) url('{{ asset('images/auth/brand.png') }}') center/cover no-repeat;
            min-height: 100vh;
        }

        /* left form panel */
        .form-side {
            flex: 1 1 52%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .form-box { width: 100%; max-width: 430px; }

        h1 { font-size: 2rem; font-weight: 800; margin: 0 0 .6rem; text-align: center; }
        .subtitle { color: var(--muted); text-align: center; margin: 0 0 2rem; font-size: .95rem; line-height: 1.7; }

        label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: .4rem; }

        /* input with leading icon + optional trailing (eye) */
        .field { position: relative; margin-bottom: 1rem; }
        .field .icon {
            position: absolute; inset-inline-end: .9rem; top: 50%; transform: translateY(-50%);
            width: 20px; height: 20px; color: var(--muted); pointer-events: none;
        }
        .field .eye {
            position: absolute; inset-inline-start: .9rem; top: 50%; transform: translateY(-50%);
            width: 20px; height: 20px; color: var(--muted); background: none; border: 0; cursor: pointer; padding: 0;
        }
        .input {
            width: 100%; height: 52px; border: 1px solid var(--border); border-radius: 12px;
            background: var(--field); padding: 0 2.7rem 0 2.7rem; font-size: 1rem; font-family: inherit; color: var(--ink);
            transition: border-color .15s, box-shadow .15s;
        }
        .input::placeholder { color: #9ca3af; }
        .input:focus { outline: none; border-color: var(--green); box-shadow: 0 0 0 3px rgba(75,90,43,.12); }
        .field.no-icon .input { padding-inline-end: 1rem; }

        .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }

        .btn {
            width: 100%; height: 54px; border: 0; border-radius: 14px; cursor: pointer;
            background: var(--green); color: #fff; font-family: inherit; font-size: 1.05rem; font-weight: 700;
            transition: background .15s, transform .05s;
        }
        .btn:hover { background: var(--green-dark); }
        .btn:active { transform: translateY(1px); }

        .link { color: var(--green); font-weight: 700; text-decoration: none; }
        .link:hover { text-decoration: underline; }

        .between { display: flex; justify-content: space-between; align-items: center; margin: -.2rem 0 1.4rem; }
        .center-link { text-align: center; margin-top: 1.6rem; font-weight: 600; color: var(--muted); }

        .divider { display: flex; align-items: center; gap: 1rem; color: var(--muted); margin: 1.6rem 0; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        .social { display: flex; align-items: center; justify-content: center; gap: .6rem;
            width: 100%; height: 52px; border: 1px solid var(--border); border-radius: 12px;
            background: #fff; color: var(--ink); font-family: inherit; font-size: .95rem; font-weight: 600;
            text-decoration: none; margin-bottom: .75rem; transition: background .15s, border-color .15s; cursor: pointer; }
        .social:hover { background: #faf9f5; border-color: #d1d5db; }
        .social img { width: 20px; height: 20px; }

        .check { display: flex; align-items: center; gap: .5rem; font-size: .9rem; font-weight: 600; margin-bottom: 1.2rem; }
        .check input { width: 18px; height: 18px; accent-color: var(--green); }

        .alert { padding: .8rem 1rem; border-radius: 10px; font-size: .9rem; margin-bottom: 1.2rem; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .alert-success { background: #dcfce7; color: #166534; }
        .err { color: #dc2626; font-size: .8rem; margin: -.6rem 0 .8rem; }

        /* OTP boxes */
        .otp { display: flex; gap: .6rem; justify-content: center; margin: 1.5rem 0 1rem; direction: ltr; }
        .otp input {
            width: 56px; height: 56px; text-align: center; font-size: 1.4rem; font-weight: 700;
            border: 1px solid var(--border); border-radius: 12px; font-family: inherit;
        }
        .otp input:focus { outline: none; border-color: var(--green); box-shadow: 0 0 0 3px rgba(75,90,43,.12); }
        .timer { text-align: center; color: var(--muted); font-weight: 700; margin-top: 1rem; letter-spacing: 1px; }

        @media (max-width: 900px) {
            .brand { display: none; }
            .form-side { flex: 1 1 100%; }
        }
    </style>
</head>
<body>
    <div class="auth-wrap">
        <aside class="brand" aria-hidden="true"></aside>
        <main class="form-side">
            <div class="form-box">
                @if ($heading)<h1>{{ $heading }}</h1>@endif
                @if ($subtitle)<p class="subtitle">{{ $subtitle }}</p>@endif

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                @if ($errors->any() && ! $errors->has('_hidden'))
                    <div class="alert alert-error">{{ $errors->first() }}</div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
    <script>
        function togglePw(id) {
            const el = document.getElementById(id);
            if (el) el.type = el.type === 'password' ? 'text' : 'password';
        }
    </script>
    @stack('scripts')
</body>
</html>
