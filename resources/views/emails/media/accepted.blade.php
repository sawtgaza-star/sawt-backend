{{-- Media consultation → accepted --}}
@php
    /** @var string $name */
    /** @var string $service */
    /** @var string $requestUuid */
@endphp

@component('emails.layout', [
    'title' => 'تم قبول طلب الاستشارة',
    'heading' => 'مرحباً '.$name,
    'subheading' => 'تم قبول طلب حجز الاستشارة الخاص بك',
    'headerColor' => '#0F3D2E',
    'headerIcon' => '✓',
])
    <p style="margin:0 0 16px;">يسعدنا إخبارك بأنه تم قبول طلب حجز الاستشارة الخاص بك.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
        @include('emails.partials.info-row', ['label' => 'الخدمة', 'value' => $service])
        <tr><td style="height:10px;"></td></tr>
        @include('emails.partials.info-row', ['label' => 'رقم الطلب', 'value' => $requestUuid])
    </table>

    <p style="margin:0 0 16px;">سيتواصل معك فريق صوت ميديا قريباً لتحديد الموعد والتفاصيل.</p>
    <p style="margin:0;color:#6b7280;font-size:13px;">شكراً لثقتك بصوت ميديا.</p>
@endcomponent
