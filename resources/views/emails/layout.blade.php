{{-- Shared HTML shell for all Sawt transactional emails (RTL Arabic). --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $title ?? config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Tahoma,Arial,sans-serif;-webkit-text-size-adjust:100%;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 24px rgba(15,61,46,0.08);">
                {{-- Header band (accept = green, reject = red via $headerColor) --}}
                <tr>
                    <td style="background:{{ $headerColor ?? '#0F3D2E' }};padding:28px 24px;text-align:center;">
                        <div style="display:inline-block;width:56px;height:56px;line-height:56px;border-radius:14px;background:rgba(255,255,255,0.15);color:#ffffff;font-size:28px;font-weight:700;">
                            {{ $headerIcon ?? '✓' }}
                        </div>
                        <h1 style="margin:16px 0 8px;color:#ffffff;font-size:20px;line-height:1.5;font-weight:700;">
                            {{ $heading }}
                        </h1>
                        @if (! empty($subheading))
                            <p style="margin:0;color:rgba(255,255,255,0.88);font-size:14px;line-height:1.7;">
                                {{ $subheading }}
                            </p>
                        @endif
                    </td>
                </tr>

                {{-- Case-specific body --}}
                <tr>
                    <td style="padding:28px 24px;color:#1f2937;font-size:15px;line-height:1.8;text-align:right;">
                        {{ $slot }}
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="padding:16px 24px 24px;background:#f9fafb;text-align:center;color:#6b7280;font-size:12px;line-height:1.6;">
                        منصة صوت — حاضنة المحتوى
                        <br>
                        <a href="{{ \App\Support\FrontendUrl::root() }}" style="color:#0F3D2E;text-decoration:none;">
                            {{ parse_url(\App\Support\FrontendUrl::root(), PHP_URL_HOST) ?: \App\Support\FrontendUrl::root() }}
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
