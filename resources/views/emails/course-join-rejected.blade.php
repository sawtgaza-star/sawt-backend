@php
    /** @var string $name */
    /** @var string $courseTitle */
    /** @var string|null $reason */
    /** @var string $incubatorUrl */
@endphp

@component('emails.layout', [
    'title' => 'بخصوص طلب الانضمام',
    'heading' => 'مرحباً '.$name,
    'subheading' => 'تحديث بخصوص طلب انضمامك للكورس',
    'headerColor' => '#7F1D1D',
    'headerIcon' => '!',
])
    <p style="margin:0 0 16px;">نشكرك على اهتمامك بحاضنة صوت.</p>
    <p style="margin:0 0 16px;">نأسف لإبلاغك بأنه تعذّر قبول طلب انضمامك إلى الكورس في الوقت الحالي.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
        <tr>
            <td style="background:#f3f4f6;border-radius:12px;padding:14px 16px;">
                <div style="color:#6b7280;font-size:12px;margin-bottom:4px;">اسم الكورس</div>
                <div style="color:#111827;font-size:16px;font-weight:700;">{{ $courseTitle }}</div>
            </td>
        </tr>
        @if ($reason)
            <tr><td style="height:10px;"></td></tr>
            <tr>
                <td style="background:#FEF2F2;border-radius:12px;padding:14px 16px;border:1px solid #FECACA;">
                    <div style="color:#991B1B;font-size:12px;margin-bottom:4px;">السبب</div>
                    <div style="color:#7F1D1D;font-size:14px;">{{ $reason }}</div>
                </td>
            </tr>
        @endif
    </table>

    <p style="margin:0 0 22px;">يمكنك تصفح كورسات أخرى أو تقديم طلب جديد لاحقاً — نتمنى لك التوفيق.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <a href="{{ $incubatorUrl }}"
                   style="display:inline-block;background:#0F3D2E;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:999px;font-size:15px;font-weight:700;">
                    تصفح كورسات ثانية
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:24px 0 0;color:#6b7280;font-size:13px;">فريق صوت</p>
@endcomponent
