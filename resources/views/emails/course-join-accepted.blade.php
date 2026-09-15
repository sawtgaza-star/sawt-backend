@php
    /** @var string $name */
    /** @var string $courseTitle */
    /** @var string|null $location */
    /** @var string|null $startsAt */
    /** @var string $courseUrl */
    /** @var string $incubatorUrl */
@endphp

@component('emails.layout', [
    'title' => 'تم قبول طلب الانضمام',
    'heading' => 'مرحباً '.$name,
    'subheading' => 'تم قبول طلب انضمامك إلى الكورس بنجاح',
    'headerColor' => '#0F3D2E',
    'headerIcon' => '✓',
])
    <p style="margin:0 0 16px;">يسعدنا إخبارك بأنه تمت الموافقة على طلبك في حاضنة صوت.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
        <tr>
            <td style="background:#f3f4f6;border-radius:12px;padding:14px 16px;">
                <div style="color:#6b7280;font-size:12px;margin-bottom:4px;">اسم الكورس</div>
                <div style="color:#0F3D2E;font-size:16px;font-weight:700;">{{ $courseTitle }}</div>
            </td>
        </tr>
        @if ($location)
            <tr><td style="height:10px;"></td></tr>
            <tr>
                <td style="background:#f3f4f6;border-radius:12px;padding:14px 16px;">
                    <div style="color:#6b7280;font-size:12px;margin-bottom:4px;">المكان</div>
                    <div style="color:#111827;font-size:14px;">{{ $location }}</div>
                </td>
            </tr>
        @endif
        @if ($startsAt)
            <tr><td style="height:10px;"></td></tr>
            <tr>
                <td style="background:#f3f4f6;border-radius:12px;padding:14px 16px;">
                    <div style="color:#6b7280;font-size:12px;margin-bottom:4px;">تاريخ البدء</div>
                    <div style="color:#111827;font-size:14px;">{{ $startsAt }}</div>
                </td>
            </tr>
        @endif
    </table>

    <p style="margin:0 0 22px;">سيتواصل معك فريق صوت بتفاصيل الحضور والمواعيد. يمكنك أيضاً فتح صفحة الكورس من الزر أدناه.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding-bottom:10px;">
                <a href="{{ $courseUrl }}"
                   style="display:inline-block;background:#0F3D2E;color:#ffffff;text-decoration:none;padding:14px 28px;border-radius:999px;font-size:15px;font-weight:700;">
                    عرض الكورس
                </a>
            </td>
        </tr>
        <tr>
            <td align="center">
                <a href="{{ $incubatorUrl }}" style="color:#0F3D2E;font-size:13px;text-decoration:underline;">
                    تصفح كورسات ثانية
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:24px 0 0;color:#6b7280;font-size:13px;">شكراً لانضمامك إلى منصة صوت.</p>
@endcomponent
